<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employees;
use App\Models\Inventoryproductrel;
use App\Models\Invoices;
use App\Models\Product;
use App\Models\Tasks;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $invoices = Invoices::all();

        if($invoices->count() > 0){

            return response()->json([

                'status' => 200,
                'invoice' => $invoices 
            ], 200);
        }else{
            return response()->json([

                'status' => 404,
                'message' => 'No Data Found'
            ], 404);

        }
        
    }
    public function store(Request $request)
    {
        // Validation rules
        $validators = Validator::make($request->all(), [
            'invoicenumber' => 'required|numeric',
            'contacts' => 'required|string',
            'invoicedate' => 'required|date',
            'duedate' => 'required|date',
            'discount' => 'required|string',
            'discount_suffix' => 'nullable|string|in:%,"flat"',
            'currency' => 'required|string',
            'terms' => 'nullable|string',
            'tags' => 'nullable|array',
            'tax1' => 'nullable|numeric',
            'tax2' => 'nullable|numeric',
            'applydiscount' => 'boolean',
            'orgid' => 'nullable|numeric',
            // 'prod_id' => 'required|numeric|exists:jo_products,prod_id' 
        ]);
    
        // If validation fails, redirect back with errors and input
        if ($validators->fails()) {
            return redirect()->back()->withErrors($validators)->withInput();
        }
    
        try {
            // Prepare data for insertion
            $data = $request->all();
    
            // Handle checkbox, true if checked, false if unchecked
            $data['applydiscount'] = $request->has('applydiscount');
    
            // Insert data into the database
            $invoice = Invoices::create($data);
    
            // Retrieve product details if invoice ID and jo_inventoryproductrel ID are equal
            $productDetails = Inventoryproductrel::where('id', $invoice->id)->get();
            $items = [];

            foreach ($productDetails as $item) {
                $items[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description ?? '',
                    'price' => $item->price,
                    'quantity' => $item->quantity
                ];
            }
            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Invoice Added Successfully',
                'invoice' => $invoice,
                // 'productDetails' => $productDetails
            ], 200);
        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return response()->json([
                'status' => 500,
                'message' => 'Invoice Addition Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('invoices::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    

    /**
     * Show the specified resource.
     */
    public function show($id)
{
    try {
        $invoice = Invoices::findOrFail($id);
        return response()->json($invoice);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Invoice not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Server Error'], 500);
    }
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('invoices::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoices::findOrFail($id);
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete invoice', 'error' => $e->getMessage()], 500);
        }
    }
    
   

// public function fetchData(Request $request)
// {
//     try {
//         $options = [
//             ['value' => 'employee', 'label' => 'Employee'],
//             ['value' => 'projects', 'label' => 'Projects'],
//             ['value' => 'tasks', 'label' => 'Tasks'],
//             ['value' => 'product', 'label' => 'Products'],
//             ['value' => 'expense', 'label' => 'Expenses'],
//         ];
        

//         // $request->validate([
//         //     'value' => 'required|string'
//         // ]);
//         $value = 'tasks'; // This line assigns a default value for demonstration purposes

//         if ($value === 'tasks') {
//             // Fetch tasks
//             $tasks = Tasks::select('id', 'title')->get();

//             // Format tasks into options array
//             $newOptions = $tasks->map(function ($task) {
//                 return [
//                     'value' => $task->id,
//                     'label' => $task->title
//                 ];
//             });
            
//             return response()->json([
//                 'status' => 200,
//                 'options' => $newOptions
                
//             ]); 
//         } elseif (is_numeric($value)) {
//             // Validate the ID
//             // $request->validate([
//             //     'value' => 'required|integer'
//             // ]);
//             $validator = Validator::make(['value' => $value], [
//                 'value' => 'required|integer'
//             ]);
//             // Fetch the task details
//             // $task = Tasks::where($value);
//             $task = Tasks::find($value);
//             // $task = Tasks::where('id', $value)->first();
//             // $task = Tasks::findOrFail($value);
//             // $invoice = Invoice::where('id', $value)->first()

//             // $task = 1;
//             if ($task) {
//                 return response()->json([
//                     'status' => 200,
//                     'task' => [
//                         'id' => $task->id,
//                         'title' => $task->title,
//                         'description' => $task->description ?? '', // Handle potential null description
//                     ]
//                 ]);
//             } else {
//                 // Handle case where task with $value ID was not found
//                 return response()->json([
//                     'status' => 404,
//                     'error' => 'Task not found'
//                 ], 404);
//             }
//         } elseif ($value === 'product') {
//             $request->validate([
//                 'taskId' => 'required|integer'
//             ]);
//             $taskId = $request->input('taskId');

//             // Fetch products related to the task
//             $products = Product::where('task_id', $taskId)->get();
//             $productDetails = $products->map(function ($product) {
//                 return [
//                     'product_name' => $product->name,
//                     'quantity' => $product->quantity
//                 ];
//             })->toArray();

//             $no_of_product = count($productDetails);

//             return response()->json([
//                 'status' => 200,
//                 'no_of_product' => $no_of_product,
//                 'products' => $productDetails
//             ]);
//         } else {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Invalid value'
//             ]);
//         }
//     } catch (ValidationException $e) {
//         return response()->json([
//             'status' => 422,
//             'message' => 'Validation Error',
//             'errors' => $e->errors()
//         ]);
//     } catch (Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => 'An error occurred while processing the request.',
//             'error' => $e->getMessage()
//         ]);
//     }
// }

public function fetchData() {
    try {
        // Define initial options array
        $options = [
            ['value' => 'employee', 'label' => 'Employee'],
            ['value' => 'projects', 'label' => 'Projects'],
            ['value' => 'tasks', 'label' => 'Tasks'],
            ['value' => 'product', 'label' => 'Products'],
            ['value' => 'expense', 'label' => 'Expenses'],
        ];
        
        $value = 'product';

        if ($value === 'tasks') {
          
            $tasks = Tasks::select('id', 'title')->get();

            $newOptions = $tasks->map(function ($task) {
                return [
                    'value' => $task->id,
                    'label' => $task->title
                ];
            });

            return response()->json([
                'status' => 200,
                'options' => $newOptions
            ]);
        } elseif ($value === 'product') {

            $tasks = Tasks::select('id', 'title')->get();
            $newOptions = [];
           
            $products = Product::query()->get();
            foreach ($products as $product) {
                $newOptions[] = [
                    'value' => $product->id,
                    'label' => $product->name 
                ];
            }
            return response()->json([
                'status' => 200,
                'options' => $newOptions
            ]);
        }
        else {
           
            throw new \Exception('Invalid value provided.');
        }
    } catch (\Exception $e) {
       
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}

// public function getTaskDetails($value)
// {
//     try {
//         // Validate the ID
//         $validator = Validator::make(['value' => $value], [
//         'value' => 'required|integer'
//     ]);
//         // $value = 1;

//         // Fetch the task details
//         $task = Tasks::find($value);

//         if ($task) {
//             return response()->json([
//                 'status' => 200,
//                 'task' => [
//                    'id' =>$task->id,
//                     'title' => $task->title,
//                     'description' => $task->description ?? '', 
//                 ]
//             ]);
//         } else {
//             // Handle case where task with $id was not found
//             throw new \Exception('Task not found');
//         }
//     } catch (\Exception $e) {
//         // Handle exceptions here, you can log the error or return an error response
//         return response()->json([
//             'status' => 404,
//             'error' => $e->getMessage()
//         ], 404);
//     }
// }
// public function getProduct($value)
// {
//     try {
//         // Validate the ID
//         $validator = Validator::make(['value' => $value], [
//         'value' => 'required|integer'
//     ]);
//         // $value = 1;

//         // Fetch the task details
//         $product = Product::find($value);

//         if ($product) {
//             return response()->json([
//                 'status' => 200,
//                 'product' => [
//                    'id' =>$product->id,
//                     'name' => $product->name,
//                     'description' => $product->description ?? '', 
//                     'price'=>$product->price,
//                     'quantity'=>$product->quantity
//                 ]
//             ]);
//         } else {
//             // Handle case where task with $id was not found
//             throw new \Exception('Product not found');
//         }
//     } catch (\Exception $e) {
//         // Handle exceptions here, you can log the error or return an error response
//         return response()->json([
//             'status' => 404,
//             'error' => $e->getMessage()
//         ], 404);
//     }
// }
public function getDetails($type, $value)
{
    try {
        // Validate the ID
        $validator = Validator::make(['value' => $value], [
            'value' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid ID');
        }

        // Fetch the details based on the type
        if ($type === 'task') {
            $item = Tasks::find($value);
            if ($item) {
                return response()->json([
                    'status' => 200,
                    'task' => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description ?? ''
                    ]
                ]);
            } else {
                throw new \Exception('Task not found');
            }
        } elseif ($type === 'product') {
            $item = Product::find($value);
            if ($item) {
                return response()->json([
                    'status' => 200,
                    'product' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'price' => $item->price,
                        'quantity' => $item->quantity
                    ]
                ]);
            } else {
                throw new \Exception('Product not found');
            }
        } else {
            throw new \Exception('Invalid type specified');
        }
    } catch (\Exception $e) {
        // Handle exceptions here, you can log the error or return an error response
        return response()->json([
            'status' => 404,
            'error' => $e->getMessage()
        ], 404);
    }
}


// public function fetchData($value=null) {
//     try {
//         // Define initial options array
//         $options = [
//             ['value' => 'employee', 'label' => 'Employee'],
//             ['value' => 'projects', 'label' => 'Projects'],
//             ['value' => 'tasks', 'label' => 'Tasks'],
//             ['value' => 'product', 'label' => 'Products'],
//             ['value' => 'expense', 'label' => 'Expenses'],
//         ];
//         $value = 'tasks';
//         if ($value === 'tasks') {
//             // Example query to retrieve tasks (adjust according to your database schema)
//             $tasks = Tasks::select('id', 'title')->get();

//             // Format tasks into options array
//             $newOptions = $tasks->map(function ($task) {
//                 return [
//                     'value' => $task->id,
//                     'label' => $task->title
//                 ];
//             });

//             return response()->json([
//                 'status' => 200,
//                 'options' => $newOptions
//             ]);
//         } elseif ($value !== null) {
//             // Validate the ID
//             $validator = Validator::make(['value' => $value], [
//                 'value' => 'required|integer'
//             ]);

//             if ($validator->fails()) {
//                 throw new \Exception('Invalid value provided.');
//             }

//             // Fetch the task details
//             $task = Tasks::find($value);

//             if ($task) {
//                 return response()->json([
//                     'status' => 200,
//                     'task' => [
//                         'id' => $task->id,
//                         'title' => $task->title,
//                         'description' => $task->description ?? '', // Handle potential null description
//                     ]
//                 ]);
//             } else {
//                 // Handle case where task with $id was not found
//                 throw new \Exception('Task not found');
//             }
//         } else {
//             // Handle case where $value is null or not recognized
//             throw new \Exception('Invalid value provided.');
//         }
//     } catch (\Exception $e) {
//         // Handle exceptions here, you can log the error or return an error response
//         $statusCode = $e->getCode() ?: 500;
//         return response()->json([
//             'status' => $statusCode,
//             'error' => $e->getMessage()
//         ], $statusCode);
//     }
// }
}
