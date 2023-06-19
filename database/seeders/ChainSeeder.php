<?php

namespace Database\Seeders;

use Botble\Statistic\Models\Chain;
use Illuminate\Database\Seeder;

class ChainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            'COSMOS	' => 'cosmos',
            'AKASH	' => 'akash-network',
            'ASSETMANTLE	' => 'AssetMantle',
            'AURA	' => 'aura-nw',
            'AXELAR	' => 'axelarnetwork',
            'BAND	' => 'bandprotocol',
            'BITCANNA	' => 'BitCannaGlobal',
            'BITSONG	' => 'bitsongofficial',
            'CANTO	' => 'Canto-Network',
            'CHIHUAHUA	' => 'ChihuahuaChain',
            'COMDEX	' => 'comdex-official',
            'COREUM	' => 'CoreumFoundation',
            'CRESCENT 	' => 'crescent-network',
            'CRYPTO.ORG	' => 'crypto-org-chain',
            'CUDOS	' => 'CudoVentures',
            'DESMOS	' => 'desmos-labs',
            'EMONEY	' => 'e-money',
            'EVMOS	' => 'evmos',
            'FETCH.AI	' => 'fetchai',
            'GRAVITYBRIDGE	' => 'Gravity-Bridge',
            'INJECTIVE	' => 'InjectiveLabs',
            'IRIS	' => 'irisnet',
            'IXO	' => 'ixofoundation',
            'JUNO	' => 'CosmosContracts',
            'KAVA	' => 'Kava-Labs',
            'KICHAN	' => 'KiFoundation',
            'KONSTELLATION	' => 'Konstellation',
            'KUJIRA	' => 'Team-Kujira',
            'KYVE	' => 'KYVENetwork',
            'LIKECOIN	' => 'likecoin',
            'LUM	' => 'lum-network',
            'MARS	' => 'mars-protocol',
            'NEUTRON	' => 'neutron-org',
            'MEDIBLOCK	' => 'medibloc',
            'NOBLE	' => 'strangelove-ventures',
            'NYX	' => 'nymtech',
            'OMNIFIX	' => 'OmniFlix',
            'ONOMY	' => 'onomyprotocol',
            'OSMOSIS	' => 'osmosis-labs',
            'PASSAGE	' => "",
            'PERSISTENCE	' => 'persistenceOne',
            'PROVENANCE	' => 'provenance-io',
            'QUASAR	' => "",
            'QUICKSILVER	' => 'ingenuity-build',
            'REGEN	' => 'regen-network',
            'RIZON	' => 'rizon-world',
            'SENTINEL	' => 'sentinel-official',
            'SECRET	' => 'SecretFoundation',
            'SHENTU	' => 'shentufoundation',
            'SIFCHAIN	' => 'Sifchain',
            'SOMMELIER	' => 'PeggyJV',
            'STAFIHUB	' => 'stafihub',
            'STARGAZE	' => 'public-awesome',
            'STARNAME	' => 'iov-one',
            'STRIDE	' => 'Stride-Labs',
            'TERITORI	' => 'TERITORI',
            'TGRADE	' => 'confio',
            'UMEE	' => 'umee-network',
            'XPLA	' => 'xpladev'
        ]
        ;

        foreach ($arr as $name => $prefix){
            Chain::create([
                "name" => trim($name),
                "github_prefix" => trim($prefix),
            ]);
        }



    }
}
