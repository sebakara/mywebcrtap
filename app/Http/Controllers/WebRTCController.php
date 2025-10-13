<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\WebRTCSignaling;

class WebRTCController extends Controller
{
    // public function sendSignal(Request $request)
    // {
    //     event(new WebRTCSignaling($request->all(), $request->input('to')));
    //     return response()->json(['status' => 'sent']);
    // }

    public function sendSignal(Request $request)
{
    event(new WebRTCSignaling(
        $request->all(),
        $request->input('room')
    ));
    return response()->json(['status' => 'sent']);
}
}
