<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\PrototypeRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\StorePrototypeRequest;
use App\Http\Requests\UpdatePrototypeRequest;
use App\Models\Prototype;
use App\Transformers\Prototype\DataTableTransformer;
use Illuminate\Http\Request;

class PrototypeController extends Controller
{
    public function __construct(
        protected PrototypeRepository $prototypeRepository
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(request()->expectsJson()){

            $filters = json_decode($request->get('filters'));

            return ResponseJson::successfulResponse(
                Fractal::collection($this->prototypeRepository->list($filters), DataTableTransformer::class)
            );
        }

        abort(404);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrototypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Prototype $prototype)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prototype $prototype)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrototypeRequest $request, Prototype $prototype)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prototype $prototype)
    {
        //
    }
}
