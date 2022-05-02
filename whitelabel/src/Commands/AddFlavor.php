<?php

namespace Milkcan\Whitelabel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddFlavor extends Command
{
    private const PATH = 'config/flavor';

    protected $signature = "whitelabel:flavor {file} {flavorKey} {flavorValue?}";
    protected $description = "Add a flavor key pair to a flavor config file";

    public function handle()
    {
        $flavorFile = $this->argument('file').".php";
        $flavorKey = $this->argument('flavorKey');
        $flavorValue = $this->argument('flavorValue') ?: '';
        $this->addFlavor($flavorFile,$flavorKey,$flavorValue);
        $this->info("Flavor added to all ".$flavorFile." flavor configurations");
    }

    private function findFiles($fileName): array
    {
        $withFile = [];
        $directories = File::directories(AddFlavor::PATH);
        foreach ($directories as $directory){
            if(File::isFile($directory.'/'.$fileName)){
                $withFile[] = $directory . '/' . $fileName;
            }
        }
        return $withFile;
    }

    private function addFlavor($flavorFile,$flavorKey,$flavorValue){
        $filesToEdit = $this->findFiles($flavorFile);
        $flavorString = "\t\t'".$flavorKey."' => '".$flavorValue."',\n";
        foreach ($filesToEdit as $file){
            $lines = file($file);
            array_splice($lines, -1,0, $flavorString);
            $file_content = implode($lines);
            file_put_contents($file,$file_content);
        }
    }
}
