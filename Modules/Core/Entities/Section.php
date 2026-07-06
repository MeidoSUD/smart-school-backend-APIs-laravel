<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = ['section', 'is_active'];

    public function classSections()
    {
        return $this->belongsToMany(Classe::class,
        'class_sections', 'section_id',
        'class_id')->withPivot('id','class_id','section_id','is_active');
    }
}
