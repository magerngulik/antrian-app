<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Aplikasi antrean",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 * @OA\Schema(
 *     schema="CodeQueue",
 *     title="Code Queue",
 *     description="Struktur data untuk Code Queue",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID Code Queue"
 *     ),
 *     @OA\Property(
 *         property="nama",
 *         type="string",
 *         description="Nama Code Queue"
 *     ),
 * ),
 * @OA\Schema(
 *     schema="Queue",
 *     title="Queue",
 *     description="Struktur data untuk antrian",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID Antrian"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status Antrian (misalnya, 'active' atau 'completed')"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Tanggal dan waktu pembuatan Antrian"
 *     ),
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
