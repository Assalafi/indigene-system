<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class LgaProfile extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'lga_id', 'display_name', 'office_address', 'support_phone', 'support_email',
        'primary_colour', 'secondary_colour', 'logo_file_id', 'coat_of_arms_file_id',
        'certificate_heading', 'certificate_body_template', 'footer_text', 'status', 'version_no',
        'effective_from', 'effective_to', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function logoFile()
    {
        return $this->belongsTo(FileAsset::class, 'logo_file_id');
    }

    public function coatOfArmsFile()
    {
        return $this->belongsTo(FileAsset::class, 'coat_of_arms_file_id');
    }
}
