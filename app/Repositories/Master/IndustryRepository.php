<?php
namespace App\Repositories\Master;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use  App\Lib\StrUtil;

class IndustryRepository implements IndustryRepositoryInterface
{
    /**
     * @var Industry
     */
    private $project;

    public function __construct(Industry $project) {
        $this->project = $project;
    }

    /**
     * 業種を全件取得する
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->project->orderBy('updated_at', 'asc')->get();//->orderBy('updated_at', 'desc');
    }

    /**
     * 業種を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(array $data): Industry
    {

        $project = $this->project->create($data);
        return $project;
    }

    public function findById($id): Industry
    {
        $records =  Industry::find($id);
        return $records;

    }



}
