<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Estacione
 * 
 * @property int $id
 * @property string $nombre
 * @property string $idema
 * @property string $provincia
 * @property float $latitud
 * @property float $longitud
 * @property int $altitud
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Estacione extends Model
{
	protected $table = 'estaciones';

	protected $casts = [
		'latitud' => 'float',
		'longitud' => 'float',
		'altitud' => 'int'
	];

	protected $fillable = [
		'nombre',
		'idema',
		'provincia',
		'latitud',
		'longitud',
		'altitud'
	];
}
