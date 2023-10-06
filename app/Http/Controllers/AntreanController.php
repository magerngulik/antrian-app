<?php

namespace App\Http\Controllers;

use session;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Queue;
use App\Models\RoleUser;
use App\Models\CodeQueue;
use Laravel\Dusk\Browser;
use App\Models\Assignment;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Http\Resources\AssignmentResource;

 
class AntreanController extends Controller
{
    
    public function index()
    {
        $value = session('key');
        $value += 1;
        session(['key' => $value]);
        return $value;
    }

    /**
     * @OA\Get(
     *     path="/api/pick-queue/{id}",
     *     operationId="pickQueue",
     *     tags={"Queue"},
     *     summary="Mengambil antrian untuk diproses",
     *     description="Mengambil antrian yang siap untuk diproses berdasarkan ID assignment. Jika antrian dengan assignment yang sama sedang diproses, akan mengembalikan pesan kesalahan.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id assignment bisa di dapatkan ketika login, jika null bisa di ambil ketia pick role",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item antrian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Pesan sukses"),
     *             @OA\Property(property="data", ref="#/components/schemas/Queue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Selesaikan dulu proses yang sedang anda jalankan"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tidak ada antrian yang tersedia"
     *     )
     * )
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function pickQueue($id)
    {
        $today = now()->toDateString();
        $assignment = $id;
        $dataUser = Assignment::with('role.code')->find($id);
        
        $queueItems = Queue::whereDate('created_at', $today)
            ->where('status', 'waiting')
            ->where('assignments_id', null)
            ->get();
    
        if ($queueItems->isEmpty()) {
            return response()->json(['message' => 'Tidak ada antrian yang tersedia'], 404);
        }
    
        $assingmentKode = $dataUser->role->code->queue_code;
        $status = false;
    
        foreach ($queueItems as $queueItem) {
            $userCode = $queueItem->kode[0];    
            if ($assingmentKode == $userCode) {
                $existingItemWithSameAssignment = Queue::whereDate('created_at', $today)
                    ->where('status', 'process')
                    ->where('assignments_id', $assignment)
                    ->exists();
    
                if ($existingItemWithSameAssignment) {
                    return response()->json(['message' => 'Selesaikan dulu proses yang sedang anda jalankan'], 400);
                }
                $queueItem->update([
                    'assignments_id' => $assignment,
                    'status' => 'process'
                ]);
                $data = [
                    'message' => 'Item antrian berhasil diambil',
                    'data' => $queueItem
                ];
    
                return response()->json($data, 200);
            } else {
                $status = true;
            }
        }
    
        if ($status) {
            return response()->json(['message' => 'Untuk bagian anda belum ada antri yang tersedia'], 404);
        }
    }

    //melihat user sedang melayain costumer yang mana
    public function viewQueueUser($id){
        $today = Carbon::now()->toDateString();
        $queue = Queue::where('assignments_id',$id)->where('status', 'process')->whereDate('created_at', $today)->first(); 
        $dataUser = Assignment::with('role.code')->find($id);
        $kode = $dataUser->role->code->queue_code; // isi nya kode seperti:B
        $countQueue = Queue::whereDate('created_at', $today)
                            ->where('status', 'waiting')
                            ->where('kode', 'like', $kode . '%') 
                            ->orderBy('updated_at', 'desc')
                            ->count();
        $data = [
                  "data" => [
                    "queue" => $queue,
                    "last_queue" => $countQueue
                  ]
            ]; 
        return response()->json($data, 200);
    }

    //update status queue yang sedang di prosess oleh user
    public function confirmQueueUser($id){
        $today = Carbon::now()->toDateString();
        $queue = Queue::where('assignments_id',$id)->where('status', 'process')->whereDate('created_at', $today)->first(); 
     
        if ($queue) {
            $updateResult = $queue->update([
                'status' => 'complete'
            ]);
            if ($updateResult) {
                $data = [
                    "message" => "Konfirmasi berhasil"
                ];
                return response()->json($data, 200);
            } else {
                $data = [
                    "message" => "Gagal melakukan pembaruan"
                ];
                return response()->json($data, 500); // atau kode status lain sesuai dengan kebutuhan Anda
            }
        } else {
            $data = [
                "message" => "Belum pelayanan yang harus di sudahi"
            ];
            return response()->json($data, 404); // Data tidak ditemukan
        }        
    }

     //update status queue yang sedang di prosess oleh user
     public function skipQueueUser($id){
        $today = Carbon::now()->toDateString();
        $queue = Queue::where('assignments_id',$id)->where('status', 'process')->whereDate('created_at', $today)->first(); 
     
        if ($queue) {
            $updateResult = $queue->update([
                'status' => 'skip'
            ]);
            if ($updateResult) {
                $data = [
                    "message" => "Skip berhasil"
                ];
                return response()->json($data, 200);
            } else {
                $data = [
                    "message" => "Gagal melakukan Skip"
                ];
                return response()->json($data, 500); // atau kode status lain sesuai dengan kebutuhan Anda
            }
        } else {
            $data = [
                "message" => "Tidak ada data yang bisa di skip"
            ];
            return response()->json($data, 404); // Data tidak ditemukan
        }        
    }





    public function testViewQueue(){
        $today = Carbon::now()->toDateString();
        $last_queue = Queue::with('assignment')
                            ->whereDate('created_at', $today)
                            ->where('status', 'process')
                            ->orderBy('updated_at', 'desc')
                            ->first();
        if (empty($last_queue)) {
                $data = [
                    "data" => null,
                    "message" => "Belum ada antrian hari ini"
                ];
            return response()->json($data, 200);
        }

                            
        $role = RoleUser::all();
        $assignment = Assignment::with('role', 'user')->whereDate('created_at', $today)->get();
        $queue = Queue::with('assignment')->whereDate('created_at', $today)
                ->where('status', 'process')
                ->get();
        $result = [];
        foreach ($role as $r) {
            $assignmentData = $assignment->where('role_users_id', $r->id)->first();
            $queueData = null; // Inisialisasi $queueData dengan null
            // Jika $assignmentData tidak null, cari data queue yang sesuai
            if ($assignmentData) {
                $queueData = $queue->where('assignments_id', $assignmentData->id)->first();
            }
            // Menambahkan data ke array hasil
            $result[] = [
                'id' => $r->id,
                'nama_role' => $r->nama_role,
                'code_id' => $r->code_id,
                'name' => $assignmentData ? $assignmentData->user->name : null,
                'queue' => $queueData ? [
                    'id' => $queueData->id,
                    'kode' => $queueData->kode,
                    'status' => $queueData->status,
                ] : null,
            ];
        }
        $data = [
            "data"  => [
                "last" => [
                    "kode" =>  $last_queue->kode,
                    "status" =>  $last_queue->status,
                    "nama_role" =>  $last_queue->assignment->role->nama_role,
                ],
                "user_aktif" => $result,
            ]          
        ];
        return response()->json($data, 200);
    }


    public function viewQueueList(){
        $today = Carbon::now()->toDateString();
        $queue = Queue::with('assignment')->whereDate('created_at', $today)
                ->where('status', 'process')
                ->first();
        if (!$queue) {
            return response()->json(["message" => "belum ada antrian yang tersedia"], 400);
        }    
        
        $assignments = Assignment::with(['role', 'user', 'queue'])
        ->whereDate('created_at', $today)
        ->get();

        $data =[
            "last" => [
                "kode" =>  $queue->kode,
                "status" =>  $queue->status,
                "nama_role" =>  $queue->assignment->role->nama_role,
            ],
            "data"=> $assignments,
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/list-queue",
     *     operationId="listQueue",
     *     tags={"Queue"},
     *     summary="Mengambil daftar antrian berdasarkan status",
     *     description="Mengambil daftar antrian berdasarkan status yang diberikan dan tanggal saat ini.",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status antrian yang akan diambil (misalnya, 'waiting', 'process' atau 'complete')",
     *         required=true,   
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar antrian berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Queue"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Permintaan tidak valid: Status antrian harus disediakan"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    

    public function listQueue(Request $request){
        $status = $request->input('status');
        $today = Carbon::now()->toDateString();
      
        $queue = Queue::whereDate('created_at', $today)
                ->where('status', $status)
                ->get();
        $data = [
            'data' => $queue,
           
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/chose-assignment",
     *     summary="Choose Assignment",
     *     description="Endpoint untuk memilih penugasan oleh pengguna.",
     *     tags={"Assignment"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data yang diperlukan untuk memilih penugasan.",
     *         @OA\JsonContent(
     *             required={"id", "roles"},
     *             @OA\Property(property="id", type="integer", example=1, description="ID pengguna."),
     *             @OA\Property(property="roles", type="integer", example=2, description="ID peran yang dipilih untuk penugasan."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil memilih penugasan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1, description="ID penugasan."),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID pengguna."),
     *             @OA\Property(property="role_users_id", type="integer", example=2, description="ID peran yang dipilih untuk penugasan."),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-28 12:34:56", description="Waktu penugasan dibuat."),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-09-28 12:34:56", description="Waktu penugasan diperbarui."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Gagal memilih penugasan karena validasi gagal atau data sudah ada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Anda sudah mengambil role pada hari ini", description="Pesan error jika   pengguna sudah mengambil penugasan pada hari ini."),

     *         ),
     *     ),
     * )
     */

    public function choseAssignment(Request $request){
        $request->validate([
            'id' => 'required',
            'roles' => 'required',
        ]); 
        $id = $request->input('id');
        $roles = $request->input('roles');
        $today = Carbon::now()->toDateString();

        $existing = Assignment::where('user_id', $id)
        ->whereDate('created_at', $today)
        ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Anda sudah mengambil role pada hari ini',
            ], 400); 
        }    

        $existingAssignment = Assignment::where('user_id', $id)
            ->where('role_users_id', $roles)
            ->whereDate('created_at', $today)
            ->first();
       
        if ($existingAssignment) {
            if ($existingAssignment->role_users_id != $roles ) {            
                return response()->json([
                    'error' => 'Anda sudah mengambil role lain pada hari ini',
                    ], 400);  
            } else{
                              
                return response()->json([
                    'error' => 'Data sudah ada untuk hari ini',
                    'data' => $existingAssignment
                ], 400);  
            }
        }

        $dataNew = Assignment::create([
            "user_id" => $id,
            "role_users_id" => $roles
        ]);
        $dataNew->refresh();
        $newlyCreatedId = $dataNew->id;
        $today = Carbon::today();
        $assignment = Assignment::find($newlyCreatedId);
        $layanan = $assignment?->role->code->name ?? null;
        $unit = $assignment?->role->nama_role ?? null;
        $assignedRoles = $assignment?->id ?? null;
        $data = [
            "new_data" => $dataNew,
            "assignment" => [
                "id" =>$newlyCreatedId,
                "layanan" =>$layanan,
                "unit" =>$unit,
            ]
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/get-role",
     *     summary="Get Available Roles for Assignment",
     *     description="Setelah User login, langkah selanjutnya adalah lihat apakah sudah ada assignment, jika belum ada ambil role nya di sini, isi endpoint ini adalah role yang tersedia di dalam api",
     *     tags={"Assignment"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mendapatkan peran yang tersedia.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1, description="ID peran pengguna."),
     *                 @OA\Property(property="name", type="string", example="Editor", description="Nama peran pengguna."),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Gagal autentikasi, token tidak valid atau tidak ada token.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated", description="Pesan error."),
     *         ),
     *     ),
     * )
     */

    public function getRolesAssignment(){
        $today = Carbon::today();
        $assignedRoles = Assignment::whereDate('created_at', $today)->pluck('role_users_id')->toArray();
        $availableRoles = RoleUser::whereNotIn('id', $assignedRoles)->get();
        $data = ["data" => $availableRoles];
        return response()->json($data , 200);
    }
   

    public function getAssignment(){
       $today = Carbon::today(); 

       $assignment = Assignment::with(['role:id,nama_role', 'user:id,name'])
       ->whereDate('created_at', $today)
       ->get();
       $formattedAssignment = $assignment->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'role_users_id' => $item->role_users_id,
                'role' => $item->role->nama_role,
                'user' => $item->user->name,
            ];
        });

        $data =["data" => $formattedAssignment];
        return response()->json($data, 200);
    }
  
    public function getAssignmentSingle($id){
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $data = [
            'data' => $assignment
        ];
        return response()->json($data, 200);
    }
    
    public function deleteAssignment($id){
        $assignment = Assignment::find($id);    
        if (!$assignment) {
            return response()->json(['message' => 'Assignment tidak ditemukan'], 404);
        }
        $assignment->delete();
        return response()->json(['message' => 'Assignment berhasil dihapus'], 200);
    }
    
    /**
     * @OA\Get(
     *     path="/api/costumer-queue",
     *     summary="Get customer queue data",
     *     description="Mendapatkan seluruh data antrian yang ada pada hari ini",
     *     operationId="getCostumerQueue",
     *     tags={"Customer"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="kode", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="assignments_id", type="integer", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */

    public function costumerQueue(){
        $today = Carbon::today();
        $codeQueue = Queue::whereDate('created_at', $today)->get();
        $data =[
            "data" => $codeQueue
        ];
         return response()->json($data, 200);  
    }

   
    /**
     * @OA\Get(
     *     path="/api/costumer-queue/{id}",
     *     summary="Create a new customer queue",
     *     description="Untuk membuat kode antrian sesuai id kode yang di pilih, untuk id nya bisa di dapatkan pada costumer code",
     *     operationId="createCustomerQueue",
     *     tags={"Customer"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the code queue",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Queue created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="kode", type="string"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     * )
     */

    public function costumerQueueCreate($id){
        $codeQueue = CodeQueue::find($id);
        if (!$codeQueue) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $kode = $codeQueue->queue_code;
        $today = Carbon::now()->toDateString();
        $assignmentsToday = Queue::whereDate('created_at', $today)
            ->where('kode', 'like', $kode . '%') 
            ->get();
        if ($assignmentsToday->isEmpty()) {
            $dataKode = "${kode}001";
            
            $data = Queue::create([
                "kode" => $dataKode,
                "status" => "waiting"
            ]);
            return response()->json($data, 200);
        }else{
            $lastData = $assignmentsToday->last();
            $lastKode = $lastData->kode;
            $lastThreeChars = substr($lastKode, -3);
            $nextNumber = (int)$lastThreeChars + 1;
            $nextKode = "${kode}" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
            $data = Queue::create([
                "kode" => $nextKode,
                "status" => "waiting"
            ]);
            return response()->json($data, 200);
        }
         return response()->json($assignmentsToday, 200);  
    }

    /**
     * @OA\Get(
     *     path="/api/code-queue",
     *     summary="Get a list of code queues",
     *     description="Retrieve a list of all code queues.",
     *     operationId="getCodeQueues",
     *     tags={"Customer"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="queue_code", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */

    public function codeQueue(){
        $data = [
           'data' => CodeQueue::all()
        ];
        return response()->json($data, 200);
    }
    /**
     * @OA\Get(
     *     path="/api/code-queue/{id}",
     *     operationId="getCodeQueueById",
     *     tags={"Code Queue"},
     *     summary="Mendapatkan informasi Code Queue berdasarkan ID",
     *     description="Mengembalikan informasi Code Queue berdasarkan ID yang diberikan.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID dari Code Queue yang akan diambil",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data Code Queue berhasil ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/CodeQueue"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data Code Queue tidak ditemukan"
     *     )
     * )
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
  
    public function codeQueueId($id){
        $codeQueue = CodeQueue::find($id);
        if (!$codeQueue) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $data = [
            'data' => $codeQueue
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/code-queue",
     *     operationId="createCodeQueue",
     *     tags={"Code Queue"},
     *     summary="Membuat Code Queue baru",
     *     description="Membuat Code Queue baru dengan nama yang diberikan.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data Code Queue yang akan dibuat",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Nama Code Queue yang akan dibuat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code Queue berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CodeQueue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal: Nama Code Queue diperlukan"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCodeQueue(Request $request){
        $request->validate([
            'name' => 'required',
        ]); 
        $name = $request->input('name');
        $latestCode = CodeQueue::orderBy('queue_code', 'desc')->first();    
        if ($latestCode) {
            $lastLetter = substr($latestCode->queue_code, -1);
            $nextLetter = chr(ord($lastLetter) + 1);
        } else {
            $nextLetter = 'A';
        }
        $newCodeQueue = new CodeQueue();
        $newCodeQueue->name = $name;
        $newCodeQueue->queue_code = $nextLetter;
        $newCodeQueue->save();
        
        $data = [
            "data" => $newCodeQueue
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/code-queue/{id}",
     *     operationId="updateCodeQueue",
     *     tags={"Code Queue"},
     *     summary="Memperbarui informasi Code Queue berdasarkan ID",
     *     description="Memperbarui informasi Code Queue berdasarkan ID yang diberikan.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID dari Code Queue yang akan diperbarui",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data Code Queue yang akan diperbarui",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Nama yang akan digunakan untuk memperbarui Code Queue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data Code Queue berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/CodeQueue")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data Code Queue tidak ditemukan"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal: Nama Code Queue diperlukan"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateCodeQueue(Request $request, $id){
        $request->validate([
            'name' => 'required',
        ]); 
        $name = $request->input('name');
    
        $codeQueue = CodeQueue::find($id);
        
        if (!$codeQueue) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    
        $codeQueue->name = $name;
        $codeQueue->save();
        
        $data = [
            "data" => $codeQueue
        ];
        return response()->json($data, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/code-queue/{id}",
     *     operationId="deleteCodeQueue",
     *     tags={"Code Queue"},
     *     summary="Menghapus Code Queue berdasarkan ID",
     *     description="Menghapus Code Queue berdasarkan ID yang diberikan.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID dari Code Queue yang akan dihapus",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data Code Queue berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data Code Queue tidak ditemukan"
     *     )
     * )
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCodeQueue($id){
        $codeQueue = CodeQueue::find($id);
        if (!$codeQueue) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $codeQueue->delete();
        return response()->json(['message' => 'Data berhasil dihapus'], 200);
    }

}
