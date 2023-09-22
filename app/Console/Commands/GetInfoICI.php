<?php

namespace App\Console\Commands;

use Botble\Statistic\Models\Chain;
use Botble\Statistic\Models\ChainResource;
use Illuminate\Console\Command;

class GetInfoICI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ici:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = json_decode(file_get_contents(public_path("docs/json/ici.json")));

        foreach ($data as $i => $chain){
            switch ($chain->title){
                case "Stargaze": {
                    $this->updateInfo($data[$i], 53);
                    break;
                }
                case "PlanqNetwork": {
                    $this->updateInfo($data[$i], 69);
                    break;
                }
                case "Juno Network": {
                    $this->updateInfo($data[$i], 24);
                    break;
                }
                case "CUDOS": {
                    $this->updateInfo($data[$i], 15);
                    break;
                }
                case "Sommelier": {
                    $this->updateInfo($data[$i], 51);
                    break;
                }
                default: {
                    if ($found = Chain::where("name", "like", "%" . $chain->title ."%")->first()) {
                        $this->updateInfo($data[$i], $found->id);
                        break;
                    }
                    $cutString = explode(" ", $chain->title);
                    if ($found = Chain::where("name", "like", "%$cutString[0]%")->first()) {
                        $this->updateInfo($data[$i], $found->id);
                        break;
                    }
                }
            }
        }
    }

    private function updateInfo($ici, $chain_id)
    {
        $chain = Chain::whereId($chain_id)->first();
        $chain->description = $ici->summary;
        $chain->refer_ici = $ici->url;
        $chain->save();

        foreach ($ici->resources as $resource){
            if (!$exist = ChainResource::where("chain", $chain->id)->where("refer_ici", $resource->url)->first()){
                $exist = new ChainResource();
                $exist->chain = $chain->id;
                $exist->refer_ici = $resource->url;
            }
            $exist->name = $resource->title;
            $exist->image = $resource->image;
            $exist->category = $resource->type;
            $exist->created_date = date("Y-m-d", strtotime($resource->publishDate));
            $exist->save();
        }
    }
}
