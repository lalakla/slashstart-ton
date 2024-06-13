<?php namespace Services\Models;


use CodeIgniter\Model;


class Category extends \CodeIgniter\Model
{

	protected $table      = 'shop_categories';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['shop_id', 'active', 'parent_id', 'priority', 'photo', 'title', 'content', 'cashback'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;




    public function getTree($shopId = 0, $parentId = 0, $active = 0)
    {
    	$query = $this->select('id, title, photo, active')->where(['parent_id' => $parentId, 'shop_id' => $shopId])->orderBy('priority DESC, title ASC');
    	if ($active)
    		$query = $query->where('active', 1);

    	$categories = $query->findAll();

    	// p($categories);

    	foreach ($categories as $k=>$v)
    	{
    		$categories[$k]['children'] = $this->getTree($shopId, $v['id'], $active);
    	}

    	return $categories;
    }


    public function getByPath($shopId, $path, $delim = '/')
    {
    	$tree = $this->getTree($shopId);

    	$flat = getFlattenTreeTitles($tree, 'title', 'children', '///');

    	foreach ($flat as $id=>$p)
    	{
    		if ($path == $p)
    		{
    			$result = $this->limit(1)->find( $id );

    			break;
    		}
    	}

    	return $result;
    }


    public function getSelectTree($tree = [], $selected = [], $attributes = '')
    {
    	$html = $attributes !== false ? '<select ' . $attributes . '>' : '';


    	$html .= getSelectTreeOptions($tree, $selected);


	    if ($attributes !== false)
	    	$html .= '</select>';


    	return $html;
    }




}

