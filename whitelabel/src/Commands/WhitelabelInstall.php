<?php

namespace Milkcan\Whitelabel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Milkcan\Whitelabel\Models\WhitelabelDomain;

class WhitelabelInstall extends Command
{
    private const PATH = 'config/flavor';
    private const BASE = 'config/flavor/base';
    private const BASE_FOLDER = 'base';
    private const WHITELABEL_FOLDER = 'whitelabel';
    private const WHITELABEL_DOMAIN = 'white-label.test';
    private const WHITELABEL = "config/flavor/whitelabel";
    private const EXAMPLE_CONTENT_BASE = "<?php\n\n\treturn[\n\t\t'flavor-test'=>'Base Text',\n];";
    private const EXAMPLE_CONTENT_WHITE_LABEL = "<?php\n\n\treturn[\n\t\t'flavor-test'=>'White Label Text',\n];";
    private const CONTENT = "<?php\n\n\treturn[\n\t\t //add flavor strings here \n\t];";

    protected $signature = "whitelabel:install {domain}";
    protected $description = "Install the whitelabel package";

    public function handle(){
        $domain = $this->argument('domain');
        if (! $this->configExists()) {
            $this->createFlavor();
            if($this->addDefaultToDb($domain)){
                $this->info('Whitelabel Package Installed!');
            }
            else{
                $this->error('Installation failed');
                $this->clearInstall();
            }

        }
        else{
            $this->warn('Flavor directory already exists in the config folder.');
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting flavor configuration...');
                $this->createFlavor();
                $this->info('Flavor configuration overwritten');
                WhitelabelDomain::truncate();
                if($this->addDefaultToDb($domain)){
                    $this->info('Whitelabel Package Installed');
                }
                else{
                    $this->error('Installation failed');
                    $this->clearInstall();
                }
            }
            else{
                $this->warn("Flavor directory not overwritten");
                if($this->addDefaultToDb($domain)){
                    $this->info('Whitelabel Package Installed');
                }
            }
        }

    }

    /**
     * @return bool
     * Adds base domain and whitelabel domain defaults and returns success or fail
     */
    private function addDefaultToDb($domain): bool
    {
        if($this->domainVerified($domain)){
            if(Schema::hasTable('whitelabel_domains')){

                WhitelabelDomain::create([
                    'domain'=>$domain,
                    'folder'=>WhitelabelInstall::BASE_FOLDER
                ]);
                WhitelabelDomain::create([
                    'domain'=>WhitelabelInstall::WHITELABEL_DOMAIN,
                    'folder'=>WhitelabelInstall::WHITELABEL_FOLDER
                ]);
                return true;
            }
            else{
                $this->error("Please make sure to run migrations");
                return false;
            }
        }
        return false;
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

    /**
     * @return bool
     * Checks to see if flavor config directory exists
     */
    private function configExists(): bool
    {
        return File::isDirectory(WhitelabelInstall::PATH);
    }

    /**
     * @return bool
     * Checks if user wants to overwrite existing flavor config directory
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm(
            'Flavor config already exists. Do you want to overwrite it?',
            false
        );
    }

    /**
     * @return void
     * Create the base flavor directories and the default domain directories
     */
    private function createFlavor(){
        if($this->configExists()){
            File::deleteDirectory(WhitelabelInstall::PATH);
        }

        File::makeDirectory(WhitelabelInstall::PATH); //create flavor directory
        File::makeDirectory(WhitelabelInstall::BASE); // create base directory
        File::makeDirectory(WhitelabelInstall::WHITELABEL); //create whitelabel directory

        $this->makeDefaultFiles();

        $this->info('Flavor config created');
    }

    private function clearInstall(){
        if ($this->configExists()){
            File::deleteDirectory(WhitelabelInstall::PATH);
        }
        $this->error("Installation canceled");
    }

    /**
     * @return void
     * Fill the domain directories with the  default flavor files
     */
    private function makeDefaultFiles(){
        //create Example Text Files with example content
        File::put(WhitelabelInstall::BASE.'/text.php', WhitelabelInstall::EXAMPLE_CONTENT_BASE); //base directory
        File::put(WhitelabelInstall::WHITELABEL.'/text.php', WhitelabelInstall::EXAMPLE_CONTENT_WHITE_LABEL); //whitelabel directory

        //Create other default config files
        File::put(WhitelabelInstall::BASE.'/styles.php', WhitelabelInstall::CONTENT);
        File::put(WhitelabelInstall::WHITELABEL.'/styles.php', WhitelabelInstall::CONTENT);
        File::put(WhitelabelInstall::BASE.'/images.php', WhitelabelInstall::CONTENT);
        File::put(WhitelabelInstall::WHITELABEL.'/images.php', WhitelabelInstall::CONTENT);
        File::put(WhitelabelInstall::BASE.'/misc.php', WhitelabelInstall::CONTENT);
        File::put(WhitelabelInstall::WHITELABEL.'/misc.php', WhitelabelInstall::CONTENT);
    }


}
