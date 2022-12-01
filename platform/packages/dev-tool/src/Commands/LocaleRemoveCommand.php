<?php

namespace Botble\DevTool\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class LocaleRemoveCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:locale:remove {locale : The locale to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a locale';

    /**
     * @return int
     */
    public function handle()
    {
        if (!$this->confirmToProceed('Are you sure you want to permanently delete?', true)) {
            return 1;
        }

        if (!preg_match('/^[a-z0-9\-]+$/i', $this->argument('locale'))) {
            $this->error('Only alphabetic characters are allowed.');
            return 1;
        }

        $defaultLocale = lang_path($this->argument('locale'));
        if (File::exists($defaultLocale)) {
            File::deleteDirectory($defaultLocale);
            $this->info('Deleted: ' . $defaultLocale);
        }

        File::delete(lang_path($this->argument('locale')) . '.json');
        $this->removeLocaleInPath(lang_path('vendor/core'));
        $this->removeLocaleInPath(lang_path('vendor/packages'));
        $this->removeLocaleInPath(lang_path('vendor/plugins'));

        $this->info('Removed locale "' . $this->argument('locale') . '" successfully!');

        return 0;
    }

    /**
     * @param string $path
     * @return int|void
     */
    protected function removeLocaleInPath(string $path)
    {
        if (!File::isDirectory($path)) {
            return 0;
        }

        $folders = File::directories($path);

        foreach ($folders as $module) {
            foreach (File::directories($module) as $locale) {
                if (File::name($locale) == $this->argument('locale')) {
                    File::deleteDirectory($locale);
                    $this->info('Deleted: ' . $locale);
                }
            }
        }

        return count($folders);
    }
}
