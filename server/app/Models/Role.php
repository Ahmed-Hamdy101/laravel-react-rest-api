<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    // use the HasFactory trait for factory support
    use HasFactory;
    // Allow mass assignment for the name field
    protected $fillable = ['name'];
    // Disable timestamps if you don't have created_at and updated_at columns
    public $timestamps = false;
    
    // Define the many-to-many relationship with Permission 
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}
