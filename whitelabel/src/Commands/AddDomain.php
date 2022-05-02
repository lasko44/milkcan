<?php

namespace Milkcan\Whitelabel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Milkcan\Whitelabel\Models\WhitelabelDomain;

class AddDomain extends Command
{
    private const PATH = 'config/flavor';
    private const CONTENT = "<?php\n\n\treturn[\n\t\t //add flavor strings here \n\t];";
    private const DEFAULT_NAMES = ['TEXT'=>'text.php','STYLES'=>'styles.php','IMAGES'=>'images.php','MISC'=>'misc.php'];

    protected $signature = "whitelabel:domain {domain} {--all} {--all-empty} {--copy=}
                                              {--default-empty} {--default-fill} {--remove}
                                              {--remove-domain} {--remove-folder}";
    protected $description = "Add a white labeled domain and default config folder for domain";

    public function handle(){

        $domain = $this->argument('domain');
        if($this->domainVerified($domain)){

            $folder = $this->makeFolderName($domain);
            $this->createDirectory($folder);
            $this->addToDB($domain, $folder);

            $options = $this->options();
            if($options['all']){
                $this->copyAll($folder);
                $this->info('Domain added with all flavor keys and values');
            }
            elseif ($options["all-empty"]){
                $this->copyAllEmpty($folder);
                $this->info('Domain added with all empty flavor files');
            }
            elseif ($options["default-empty"]){
                $this->makeEmptyDefaultFiles($folder);
                $this->info('Domain added with all empty default flavor files');
            }
            elseif ($options['default-fill']){
                $this->copyDefault($folder);
                $this->info('Domain added with all flavor keys from default flavor files');
            }
            //TODO Implement Remove Options
            else{
                $this->info('Domain and domain flavor directory added');
            }

        }
    }

    private function domainVerified($domain): bool
    {
        if ($domain == trim($domain)) {
            return true;
        }
        else{
            $this->error("Domain cannot contain spaces");
        }
        return false;
    }

    private function configExists($folder): bool
    {
        return File::isDirectory(AddDomain::PATH.'/'.$folder);
    }

    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm(
            'Flavor directory already exists for that domain. Do you want to overwrite it?',
            false
        );
    }

    public function addToDB($domain,$folder){
        WhitelabelDomain::create([
            'domain'=>$domain,
            'folder'=>$folder
        ]);
    }


    private function createDirectory($folder)
    {
        if(!$this->configExists($folder)){
            File::makeDirectory(AddDomain::PATH.'/'.$folder);
        }
        elseif ($this->shouldOverwriteConfig()){
            File::deleteDirectory(AddDomain::PATH.'/'.$folder);
            File::makeDirectory(AddDomain::PATH.'/'.$folder);
        }
        else{
            $this->error("New domain not added");
        }
    }

    private function makeEmptyDefaultFiles($folder){
        //Create other default config files
        File::put(AddDomain::PATH.'/'.$folder.'/styles.php', AddDomain::CONTENT);
        File::put(AddDomain::PATH.'/'.$folder.'/images.php', AddDomain::CONTENT);
        File::put(AddDomain::PATH.'/'.$folder.'/misc.php', AddDomain::CONTENT);

    }

    private function makeFolderName($domain): string
    {
        $str_array = explode('.',$domain);
        return $str_array[0];
    }

    private function getDirectories(): array
    {
        return File::directories(AddDomain::PATH);
    }

    private function copyAll($folder){
        File::copyDirectory($this->getDirectories()[0], AddDomain::PATH . '/' . $folder);
    }

    private function copyDefault($folder){
        $firstDirectory = $this->getDirectories()[0];
        foreach(AddDomain::DEFAULT_NAMES as $file){
            File::copy($firstDirectory.'/'.$file,AddDomain::PATH.'/'.$folder);
        }
    }

    private function copyAllEmpty($folder){
        $firstDirectory = $this->getDirectories()[0];
        $files = File::files($firstDirectory);
        $fileNames = [];
        // Get filenames
        foreach ($files as $file) {
            $fileNames[] = File::basename($file);
        }
        //Create new empty files
        foreach ($fileNames as $name) {
            File::put(AddDomain::PATH.'/'.$folder.'/'.$name,AddDomain::CONTENT);
        }
    }


}
