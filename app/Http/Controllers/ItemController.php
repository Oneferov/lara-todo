<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ItemStoreRequest;
use App\Repositories\ItemRepository;
use App\Services\ItemService;



class ItemController extends Controller
{

    public function __construct(ItemService $itemService, ItemRepository $itemRepository)
    {
        $this->service = $itemService;
        $this->repository = $itemRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->repository->getQuery()->orderBy('id', 'DESC')->get();
        return view('home', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ItemStoreRequest $request)
    {
        $model = $this->service->create($request->all());

        // $model = $this->repository->getOneById($model->id);

        return response()->json(['model' => $model, 'image' => $model->image, 'tags' => $model->tags]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(['model' => $this->repository->getOneById($id)]);

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
        $result = $this->repository->update($request->all(), $id);

        if ($result) {
            $model = $this->repository->getOneById($id);
            return response()->json([
                'success' => true,
                'model' => $model,
                'image' => $model->image,
            ]);
        }else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    public function updateImage(Request $request, $id)
    {
        $result = $this->service->update($request->all(), $id);
        if ($result) {
            return response()->json([
                'success' => true,
                'image' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json(['success' => $this->service->destroy($id)]);
    }

    public function destroyImage($id)
    {
        return response()->json(['success' => $this->service->destroyImage($id)]);
    }

    public function destroyTag($item_id, $tag_id)
    {
        return response()->json(['success' => $this->service->destroyTag($item_id, $tag_id)]);
    }

    public function storeTag(Request $request, $item_id)
    {
        $result = $this->service->update($request->all(), $item_id);

        if ($result === false) {            
            return response()->json([
                'success' => false
            ]);
        } else {
            return response()->json([
                'success' => true,
                'tags' => $result,
            ]);
        }
    }
}
