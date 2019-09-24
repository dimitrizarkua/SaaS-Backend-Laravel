<?php

namespace App\Components\Jobs\Models;

use App\Components\Contacts\Models\Contact;
use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobContactAssignment
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                           $job_id
 * @property int                           $job_assignment_type_id
 * @property int                           $assignee_contact_id
 * @property int|null                      $assigner_id
 * @property boolean                       $invoice_to
 * @property Carbon                        $created_at
 *
 * @property-read Job                      $job
 * @property-read JobContactAssignmentType $type
 * @property-read User                     $assigner
 * @property-read Contact                  $assignee
 */
class JobContactAssignment extends Model
{
    use HasCompositePrimaryKey;

    const UPDATED_AT = null;

    public $timestamps   = true;
    public $incrementing = false;

    protected $table      = 'job_contact_assignments';
    protected $touches    = ['job'];
    protected $primaryKey = ['job_id', 'job_assignment_type_id', 'assignee_contact_id'];
    protected $fillable   = [
        'job_id',
        'job_assignment_type_id',
        'assignee_contact_id',
        'invoice_to',
        'assigner_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['created_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * Job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Assignment Type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(JobContactAssignmentType::class, 'job_assignment_type_id');
    }

    /**
     * Assigner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }

    /**
     * Assignee contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'assignee_contact_id');
    }
}
