<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Method;
use App\MethodTranslation;
use App\MethodGroup;
use App\MethodGroupTranslation;

class VoteMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        $this->voteMethodsGroups();
        $this->voteMethods();

        DB::commit();
    }

    private function voteMethodsGroups() {
        $voteMethodsGroup = array(
            array(
                "id"            => 1,
                "translations"  => array(
                    array(
                        "method_group_id"   => 1,
                        "language_code"     => "en",
                        "name"              => "Web Platform",
                        "description"       => "Method for the web platform",
                    )
                )
            ),array(
                "id"            => 2,
                "translations"  => array(
                    array(
                        "method_group_id"   => 2,
                        "language_code"     => "en",
                        "name"              => "CellPhones",
                        "description"       => "Method for cellPhones",
                    )
                )
            )
        );

        foreach ($voteMethodsGroup as $voteMethodGroup) {
            $translations = $voteMethodGroup["translations"];
            unset($voteMethodGroup["translations"]);

            MethodGroup::firstOrCreate($voteMethodGroup);

            foreach ($translations as $translation) {
                $translation = array_merge(["method_group_id"=>$voteMethodGroup["id"]],$translation);
                MethodGroupTranslation::firstOrCreate($translation);
            }
        }
    }

    private function voteMethods() {
        $voteMethods = array(
            array(
                "id" 					 	=> 1,
                "method_group_id"           => 1,
                "code" 					 	=> "likes",
                "translations" 			 	=> array(
                    array(
                        "language_code" 	=> "en",
                        "name" => "Likes",
                        "description" => "Method to use Likes",
                    ),array(
                        "language_code" => "pt",
                        "name" => "Likes",
                        "description" => "Likes",
                    )
                )
            ),array(
                "id" 					 	=> 2,
                "method_group_id"           => 1,
                "code" 					 	=> "multi_voting",
                "translations" 			 	=> array(
                    array(
                        "language_code" 	=> "en",
                        "name" => "Multi Voting",
                        "description" => "Method for Multi Voting, use a number max of votes",
                    ),array(
                        "language_code" => "pt",
                        "name" => "Voto Multiplo",
                        "description" => "Voto Multiplo",
                    )
                )
            ),array(
                "id" 					 	=> 3,
                "method_group_id"           => 1,
                "code" 					 	=> "negative_voting",
                "translations" 			 	=> array(
                    array(
                        "language_code" 	=> "en",
                        "name" => "Negative Voting",
                        "description" => "Method for Negative Voting, can vote negative and ...",
                    ),array(
                        "language_code" => "pt",
                        "name" => "Voto Negativo",
                        "description" => "Voto Negativo",
                    )
                )
            ),array(
                "id" 					 	=> 4,
                "method_group_id"           => 1,
                "code" 					 	=> "rank",
                "translations" 			 	=> array(
                    array(
                        "language_code" 	=> "pt",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "cz",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "it",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "de",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "en",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "fr",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    ),array(
                        "language_code" 	=> "es",
                        "name" => "Rank Voting",
                        "description" => "Method for Rank Voting, can vote by giving a rank",
                    )
                )
            ),
        );

        foreach ($voteMethods as $voteMethod) {
            $translations = $voteMethod["translations"];
            unset($voteMethod["translations"]);

            Method::firstOrCreate($voteMethod);

            foreach ($translations as $translation) {
                $translation = array_merge(["method_id"=>$voteMethod["id"]],$translation);
                MethodTranslation::firstOrCreate($translation);
            }
        }
    }    
}
