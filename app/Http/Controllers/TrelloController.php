<?php

namespace App\Http\Controllers;

use App\Services\TrelloApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrelloController extends Controller
{
    /**
     * @var App\Services\TrelloApiService
     */
    protected $trelloApiService;

    public function __construct(TrelloApiService $trelloApiService)
    {
        $this->trelloApiService = $trelloApiService;
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        $this->trelloApiService->handleWebhook($data);

        return response()->json(['status' => 'ok']);
    }
}
