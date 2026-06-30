<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['section', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function classSections()
    {
        return $this->belongsToMany(Classe::class,
        'class_sections', 'section_id',
        'class_id')->withPivot('id','class_id','section_id','is_active');
    }
}
