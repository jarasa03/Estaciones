<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EstacionInv
 * 
 * @property int $id
 * @property string|null $nombre
 * @property string|null $idema
 * @property string|null $provincia
 * @property float|null $latitud
 * @property float|null $longitud
 * @property int|null $altitud
 *
 * @package App\Models
 */
class EstacionInv extends Model
{
	protected $table = 'estacion_inv';
	protected $primaryKey = 'id';
	public $timestamps = false;

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
	public function estado()
	
	{
		return $this->hasOne(EstacionBd::class, 'id', 'id');
	}
}
