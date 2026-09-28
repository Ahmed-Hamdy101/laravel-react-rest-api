<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    
        #[OA\Get(
        path: '/v1/products',
        security: [['bearerAuth' => []]],
        summary: 'Get products list',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retrieve All Products successfully',
            ),
        ]
    )]

    public function index()
    {
        //  req gates for view  Products
        Gate::authorize('view', Product::class);
        $products = Product::paginate(10);
        return ProductResource::collection($products);
   }
    /**
     * Display single Prodcut.
     */
    #[OA\Get(
            path: '/v1/proudcts/{id}',
            security:[['bearerAuth'=>[]]],
            summary: 'Show a single product by ID',
            tags: ['Products'],
            parameters:[
                new OA\Parameter(
                name :'id',
                description:' The Unique identifier of the product',
                in:'path',
                required:true,
                schema:new OA\Schema(type:'interger')
                )
            ], 
        responses:[
            new OA\Response(
                response: 200,
                description: 'Retrieve One Single Product successfully'
            )
         ]  
       )
    ]
    public function show(string $id)
    {
        //  req gates for view  one single Products
        Gate::authorize('view', Product::class);
        $product = Product::findOrFail($id);
        return  response()->json(
            new ProductResource($product),
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path:'/v1/products',
        summary:'Create a new product',
        security:[['bearerAuth'=>[]]],
        tags:['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content:new OA\JsonContent(
                ref: '#/components/schemas/ProductCreateRequest'
                )
            ),
        responses:[
            new OA\Response(
                response: 201,
                description: 'Product created successfully'
            )
        ]  
        )
    ]

    public function store(ProductCreateRequest $request)
    {
        //  req gates for add  Products
        Gate::authorize('edit', Product::class);
        $product = Product::create($request->only('title', 'description','image', 'price') );
        return response()->json(
            new ProductResource($product),
            Response::HTTP_CREATED
        );
    }


    /**
     * Update the specified resource in storage.
     */

    #[OA\Put(
        path: '/v1/products/{id}',
        summary: 'Update a product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'The unique identifier of the product',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ProductUpdateRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated successfully'
            )
        ]
    )]

    public function update(ProductUpdateRequest $request, string $id)
    {
        //  req gates for update  Products
        Gate::authorize('edit', Product::class);
        $product = Product::findOrFail($id);
        $product->update($request->only('title', 'description', 'image', 'price'));
        return response()->json(
            new ProductResource($product),
            Response::HTTP_OK
        ); 
    }

    /** Delete a product by ID. */
    #[OA\Delete(
        path: '/v1/products/{id}',
        summary: 'Delete a product',
        security: [['bearerAuth' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'The unique identifier of the product',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted successfully'),
            new OA\Response(response: 404, description: 'Product not found')
        ]
    )]
    public function destroy(string $id)
    {
        //  req gates for delete  Products
        Gate::authorize('edit', Product::class);
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }
}
