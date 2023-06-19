<?php
namespace App\Http\Controllers;


class APIController extends Controller{

    /**
     * @SWG\Get (
     *     path="/api/v1/chain-list",
     *     description="Get chain list",
     *     tags={"chain"},
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/commit-info",
     *     description="Get commit info for chain",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain",
     *         in="query",
     *         type="number",
     *         description="Chain id",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */


}
