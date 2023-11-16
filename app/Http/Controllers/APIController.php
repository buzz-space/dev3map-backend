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
     *     @SWG\Parameter(
     *         name="before_hours",
     *         in="query",
     *         type="number",
     *         description="Filter by hours before (day*24=hours)",
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
     *     path="/api/v1/chain/{prefix}",
     *     description="Get chain info",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="prefix",
     *         in="path",
     *         type="string",
     *         description="Chain prefix",
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
     *     path="/api/v1/summary-info",
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
     *     path="/api/v1/developer-chart",
     *     description="Get developer chart",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain",
     *         in="query",
     *         type="number",
     *         description="Chain id",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="filter",
     *         in="query",
     *         type="number",
     *         description="Filter by range (7,30)",
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
     *     @SWG\Parameter(
     *         name="with_data",
     *         in="query",
     *         type="number",
     *         description="Optional: Get addtional chain with filter category",
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
     *     path="/api/v1/ranking",
     *     description="Get ranking",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="type",
     *         in="query",
     *         type="string",
     *         description="Type of rank (rising_star,ibc_astronaut,seriousness)",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */
    /**
     * @SWG\Get (
     *     path="/api/v1/chain-repository/{chain_id}",
     *     description="Get chain repository",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain_id",
     *         in="path",
     *         type="number",
     *         description="Chain ID",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="hide_fork",
     *         in="query",
     *         type="number",
     *         description="Hide fork repository (0|1)",
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
     *     path="/api/v1/chain-developer/{chain_id}",
     *     description="Get chain developer",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain_id",
     *         in="path",
     *         type="number",
     *         description="Chain ID",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         type="string",
     *         description="Sorting contribution (DESC|ASC)",
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
     *     path="/api/v1/performance/{chain_id}",
     *     description="Get chain performance",
     *     tags={"chain"},
     *     @SWG\Parameter(
     *         name="chain_id",
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
     * @SWG\Post (
     *     path="/api/v1/add-chain",
     *     description="Add info for chain",
     *     tags={"backend"},
     *     @SWG\Parameter(
     *         name="name",
     *         in="query",
     *         type="string",
     *         description="Chain name",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="categories",
     *         in="query",
     *         type="string",
     *         description="Chain categories",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="github_prefix",
     *         in="query",
     *         type="string",
     *         description="Chain github",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="is_repo",
     *         in="query",
     *         type="number",
     *         description="Chain is repository?",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="symbol",
     *         in="query",
     *         type="string",
     *         description="Symbol of chain",
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
     *     path="/api/v1/setting",
     *     description="Get setting from cms (available: last_update)",
     *     tags={"setting"},
     *     @SWG\Parameter(
     *         name="key",
     *         in="query",
     *         type="array",
     *         items={},
     *         description="param",
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
     *     path="/api/v1/developer/{login}",
     *     description="Get developer info",
     *     tags={"developer"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="path",
     *         type="string",
     *         description="Developer login",
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
     *     path="/api/v1/developer-activity/{login}",
     *     description="Get developer activity",
     *     tags={"developer"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="path",
     *         type="string",
     *         description="Developer login",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="month",
     *         in="query",
     *         type="number",
     *         description="Filter month (default now)",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="year",
     *         in="query",
     *         type="number",
     *         description="Filter year (default now)",
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
     *     path="/api/v1/developer-contribution/{login}",
     *     description="Get developer contribution",
     *     tags={"developer"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="path",
     *         type="string",
     *         description="Developer login",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         type="string",
     *         description="Sort (DESC|ASC)",
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
     *     path="/api/v1/developer-repository/{login}",
     *     description="Get developer repository",
     *     tags={"developer"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="path",
     *         type="string",
     *         description="Developer login",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         type="string",
     *         description="Sorting (ASC,DESC)",
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
     *     path="/api/v1/developer-statistic/{login}",
     *     description="Get developer statistic",
     *     tags={"developer"},
     *     @SWG\Parameter(
     *         name="login",
     *         in="path",
     *         type="string",
     *         description="Developer login",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     ),
     * )
     */


}
