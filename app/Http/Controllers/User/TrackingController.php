<?php

namespace App\Http\Controllers\User;

use App\Tracking;
use App\TrackingNote;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $trackingList = Tracking::query()
            ->with([
                'secretary',
                'trackingNotes.user'
            ])
            ->where('status', Tracking::STATUS_PENDING)
        ;

        if (! Auth::user()->isAdmin()) {
            $trackingList->where('secretary_id', Auth::user()->id);
        }

        $trackingList = $trackingList->get();

        return view('user.tracking.index', compact('trackingList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $secretaries = User::query()->hasRole('secretary')->get();

        return view('user.tracking.create', compact('secretaries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $tracking = new Tracking($request->all());
        $tracking->status = Tracking::STATUS_PENDING;

        if (! Auth::user()->isAdmin()) {
            $tracking->secretary_id = Auth::user()->id;
        }

        $tracking->save();

        $this->sessionMessage('message.tracking.create');

        return new JsonResponse(['success' => true, 'redirect' => route('tracking.index')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Add note to tracking
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function note(Request $request)
    {
        DB::beginTransaction();

        $tracking = Tracking::query()->find($request->tracking_id);
        $tracking->status = $request->status;
        $tracking->save();

        if (! empty($request->note)) {
            $trackingNote = new TrackingNote($request->all());
            $trackingNote->user_id = Auth::user()->id;
            $trackingNote->save();
        }

        DB::commit();

        $this->sessionMessage('message.tracking.addedNote');

        return new JsonResponse(['success' => true, 'redirect' => route('tracking.index')]);
    }
}
