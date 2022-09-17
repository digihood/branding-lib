<?php

namespace Digihood\Branding\App\Console\Commands;


use Digihood\Branding\BrandingServiceProvider;
use Illuminate\Console\Command;
use Artisan;
use Carbon\Carbon;
use File;
class InstallBranding extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'install:Branding';

    /**
     * jména předdefinovanych jmen pro uživatele.
     *
     * @var array
     */

    protected $digihoodMasters = [
        'josef' => 'josef',
        'milan' => 'milan',
        'filip' => 'filip',
        'jan' => 'jan'
    ];
     /**
     * cesta k config složce 
     *
     * @var array
     */
    protected $backpack_config_files = __DIR__.'/config';
    
    /**
     * cesta k css file 
     *
     * @var array
     */
    protected $backpack_css_file = __DIR__.'/copyFiles/custom-digi.css';

     /**
     * předdefinované heslo
     *
     * @var array
     */

    protected $defaultPassword = 'Digihood';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->installBackpack();
        $this->create_users();
        $this->copyConfigsFile();
        $this->call('vendor:publish', [
            '--tag' => 'digi-models'
            ]);
         
    }
    protected function installBackpack(){
        $this->info('Instalace backpacku');
        $this->call('backpack:install', []);
        $this->alert('Instalace backpacku Dokončena');
    }
    protected function create_users(){
        $choise = $this->choice('DIGI::Kdo vytvaří projekt?',$this->digihoodMasters);
        $userClass = config('backpack.base.user_model_fqn', 'App\Models\User');
        $userModel = new $userClass();
        try {
            $user = collect([
                'name' => $this->digihoodMasters[$choise],
                'email' => $this->digihoodMasters[$choise].'@digihood.cz',
                'password' => \Hash::make($this->defaultPassword),
            ]);

            // Merge timestamps
            if ($userModel->timestamps) {
                $user = $user->merge([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            $userModel->insert($user->toArray());
            $this->info('Uživatel vytvořen');
            $next_user =$this->ask('DIGI::Chcete přidat dalšího Digi uživatele?','ne');
            if($next_user == 'ano'){
                 $this->create_users();
            } 
            
        } catch (\Throwable$e) {
            $this->errorBlock($e->getMessage());
        }
    }

    protected function copyConfigsFile(){
       try {
            if(File::exists(base_path().'/config/backpack')){
                $this->warn('DIGI::Původní konfigurační soubory odstraňeny');
                File::deleteDirectory(base_path().'/config/backpack');
            }
            $copy_config = File::copyDirectory($this->backpack_config_files,base_path().'/config');
            if($copy_config){
                $this->alert('DIGI::Konfigurační soubory vytvořeny');
            }
       } catch (\Throwable $th) {
            $this->errorBlock($th->getMessage());
       }

       try {
        if(File::exists(base_path().'/build/digihood')){
            $this->warn('DIGI::Původní css soubory odstraňeny');
            File::deleteDirectory(base_path().'/build/digihood');
        }
        $copy_config = File::copyDirectory($this->backpack_css_file,base_path().'/public/build/digihood');
        if($copy_config){
            $this->alert('DIGI::Konfigurační soubory vytvořeny');
        }
   } catch (\Throwable $th) {
        $this->errorBlock($th->getMessage());
   } 
    }
}

