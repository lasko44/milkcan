<?php

namespace Milkcan\Whitelabel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Milkcan\Whitelabel\Models\WhitelabelDomain;

class MapDomain extends Command
{
    private const PATH = 'config/flavor';
    private const BASE_FOLDER = 'config/flavor/base';

    protected $signature = "whitelabel:map {domain} {folder} ";
    protected $description = "Map a whitelabeled domain to specific folder";

    /**
     * @return void
     * Handle the command logic
     */
    public function handle(){
        $domain = $this->argument('domain');
        $folder = $this->argument('folder');

        if ($this->domainVerified($domain) && $this->folderVerified($folder)) {

            $domainExists = $this->domainExists($domain);
            $folderExists = $this->folderExists($folder);

            if($domainExists && $folderExists && $this->shouldRemap($domain)){
                $this->updateFolderOnly($domain,$folder);
            }
            elseif ($domainExists &&  !$folderExists && $this->shouldRemap($domain)){
                $this->shouldCreateNewFolder($folder);
            }
        }
        else{
            $this->error('Domain does not exist please add with php artisan whitelabel:domain "your.domain"');
        }
    }

    /**
     * @param $domain
     * @return bool
     * Check to see if the domain name is in the correct format
     */
    private function domainVerified($domain): bool
    {
        if ($domain == trim($domain)) {
            return true;
        }
        else{
            $this->error("Domain cannot contain any spaces");
        }
        return false;
    }

    /**
     * @param $folder
     * @return bool
     * Check to see if the folder already exists
     */
    private function folderExists($folder): bool
    {
        return File::isDirectory(MapDomain::PATH.'/'.$folder);
    }

    /**
     * @param $folder
     * @return bool
     * Check to see if the given folder name is in the correct format
     */
    private function folderVerified($folder): bool
    {
        if ($folder == trim($folder)) {
            return true;
        }
        else{
            $this->error("Folder name cannot contain any spaces");
        }
        return false;
    }

    /**
     * @param $domain
     * @return bool
     * Check to see if the domain is already mapped
     */
    private function domainExists($domain): bool
    {
        if (WhitelabelDomain::where('domain', 'like', '%' . $domain . '%')->get()->first() !== null) {
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @param $domain
     * @return bool
     * Confirms that the user wants to remap the domain to new or existing folder
     */
    private function shouldRemap($domain): bool
    {
        $object = WhitelabelDomain::where('domain', 'like', '%' . $domain . '%')->get()->first();
        return $this->confirm(
            '"'.$object->domain.'" is already mapped to "'.$object->folder.'" Do you want to remap it?',
            false
        );
    }

    /**
     * @param $folder
     * @return void
     * Confirm folder creation and options then call correct create folder function
     */
    private function shouldCreateNewFolder($folder): void
    {
        $create =  $this->confirm(
        '"'.$folder.'" does not exist. Would you like to create it?',
        false);

        if ($create) {
           $choice = $this->choice(
                'How would you like to create the folder',
                ['Copy Base Folder', 'Create Empty Folder'],
                0
            );

            switch ($choice) {
                case 'Copy Base Folder':
                    $this->createNewFolder($folder); //create empty folder
                    $this->copyBaseFolder($folder); //fill the folder with base flavors
                    break;

                case 'Create Empty Folder':
                    $this->createNewFolder($folder); //create empty flavor folder
                    break;

                default:
                    $this->error("Something went wrong creating the new flavor directory");

            }
        }
        $this->error('Domain not mapped');
    }

    /**
     * @param string $folder
     * @return void
     * Create a new flavor director at config/flavor/$folder
     */
    private function createNewFolder(string $folder){
        File::makeDirectory(MapDomain::PATH . '/' . $folder);
        $this->info('"'.$folder.'" created.');
    }

    /**
     * @param string $domain
     * @param string $folder
     * @return void
     * Update the folder name in the database, so it is mapped correctly
     */
    private function updateFolderOnly(string $domain, string $folder){
        WhitelabelDomain::where('domain', 'like', '%' . $domain . '%')->get()->first()
        ->update([
            'folder'=>$folder
        ]);
        $this->info('"'.$domain.'" mapped to "'.$folder.'"!');
    }

    /**
     * @param $folder
     * @return void
     * Copy the base folder flavors to new folder
     */
    private function copyBaseFolder($folder)
    {
        File::copyDirectory(MapDomain::BASE_FOLDER, MapDomain::PATH . '/' . $folder);
        $this->info('Base Flavors copied to '.$folder);
    }


}
