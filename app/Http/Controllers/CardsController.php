<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Version;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class CardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $version = $request->post('version');

        /** @var Version $version */
        $version = $version ? Version::query()->firstWhere([
            'version' => $version,
        ]) : Version::query()->firstWhere(['latest' => true]);

        $query = [
            'version' => $version->getAttributeValue('version'),
            'locale' => App::getLocale(),
            'collectible' => 1
        ];

        return response()->json(Card::where($query)->pagination(100));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $version = $request->get('version');
        $search = $request->get('term', '');

        /** @var Version $version */
        $version = $version ? Version::query()->firstWhere([
            'version' => $version,
        ]) : Version::query()->firstWhere(['latest' => true]);

        return response()->json(Card::search($search)
            ->where('version' , $version->getAttributeValue('version'))
            ->where('locale' , App::getLocale())
            ->where('collectible' , 1)
            ->paginate(100));
    }


    /**
     * Display the specified resource.
     *
     * @param string $cardCode
     * @param Request $request
     * @return JsonResponse
     */
    public function get(string $cardCode, Request $request): JsonResponse
    {
        try {
            $version = $request->post('version');
            /** @var Version $version */
            $version = $version ? Version::query()->firstWhere([
                'version' => $version,
            ]) : Version::query()->firstWhere(['latest' => true]);
            $locale = App::getLocale();
            $results = Card::query()->firstWhere([
                'cardCode' => $cardCode,
                'locale' => $locale,
                'version' => $version->version,
            ]);

            if (!$results) {
                throw new ModelNotFoundException('Card not found!');
            }

            return response()->json($results);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Card not found!',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Card $card
     * @return Response
     */
    public function update(Request $request, Card $card): Response
    {
        //
    }
}
