<?php
namespace App\Http\Controllers;


class APIController extends Controller
{

    /**
     * @SWG\Get (
     *     path="/api/v1/chain-list",
     *     description="Get chain list",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="categories",
     *         in="query",
     *         type="string",
     *         description="Filter by categories (split by `,`)",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/chain/{id}",
     *     description="Get chain info",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="number",
     *         description="Chain ID",
     *         required=true,
     *     ),
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
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/developer-info",
     *     description="Get developer info for chain",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain",
     *         in="query",
     *         type="number",
     *         description="Chain id",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/categories",
     *     description="Get categories",
     *     tags={"chain"},
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/ranking",
     *     description="Get ranking",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="type",
     *         in="query",
     *         type="string",
     *         description="Type of rank (rising_star,ibc_astronaut,seriousness)",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */


}
