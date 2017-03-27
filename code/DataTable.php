<?php

class DataTable extends DataObject
{
	private static $db = array(
		'Title' => 'Varchar(255)',
		'ShowBorders' => 'Boolean',
		'BorderColor' => 'Varchar(30)',
		'FirstRowHeader' => "Boolean",
		'CellPadding' => 'Varchar(30)',
		'Width' => 'Varchar(20)',
		'Striped' => 'Boolean'
	);

	private static $has_many = array(
		'DataTableRows' => 'DataTableRow'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
	);

	public function getCMSFields()
	{
		Requirements::css(DATATABLES_DIR."/css/DataTables_cms.css");
		$fields = parent::getCMSFields();
		$fields->insertBefore( TextField::create('ShortCode','Short Code')->setValue($this->GenerateShortCode())->setAttribute('readonly','readonly'), 'Title');
		$fields->removeByName('DataTableRows');
		if ($this->ID)
		{
			$fields->addFieldToTab('Root.Main', $gridField = GridField::create(
				'DataTableRows',
				'Rows',
				$this->DataTableRows(),
				$gf_config = GridFieldConfig_RecordEditor::create()->addComponent(
					new GridFieldSortableRows('SortOrder')
				)
			));
			$gf_config->removeComponentsByType('GridFieldAddNewButton')->addComponent(
				new GridFieldDataTableAddRowButton()
			);
			// rebuild the columns to show the actual table layout
			$columns = array('GridFieldRowNumber' => 'Row');
			for($i=0;$i<$this->ColumnCount();$i++)
			{
				$columns['GridFieldColumnPreview'.$i] = 'Column '.($i+1);
			}
			$gf_config->getComponentByType('GridFieldDataColumns')
				->setDisplayFields($columns)
				->setFieldFormatting(array('GridFieldRowNumber' => function($value,$item){ return '<strong>'.htmlspecialchars_decode($value).'</strong>'; }));
		}
		else
		{
			$fields->addFieldToTab('Root.Main', HeaderField::create('savefirst','You must save before adding rows',3) );
		}
		
		
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('Striped') );
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('FirstRowHeader') );
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('ShowBorders') );
		$fields->removeByName('BorderColor');
		$fields->addFieldToTab('Root.Style', SimpleColorPickerField::create('BorderColor')->setRightTitle('(ex. #CCCCCC, rgba(200,200,200,0.5), etc)') );
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('CellPadding')->setRightTitle('(ex. 10px, 2px 10px, etc.)') );
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('Width')->setRightTitle('(ex. auto, 1000px, 50%, etc.) Default: 100%') );
		return $fields;
	}

	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }

	public function GenerateShortCode()
	{
		if ($this->ID)
		{
			return '[datatable, id="'.$this->ID.'"]';
		}
	}
	
	public static function ParseShortCode($args, $content=null, $parser=null, $tagname=null)
	{
		Requirements::css(DATATABLES_DIR."/css/DataTables.css");
		Requirements::css(Controller::curr()->ThemeDir()."/css/DataTables.css");
		Requirements::javascript(DATATABLES_DIR."/javascript/DataTables.js");
		Requirements::javascript(Controller::curr()->ThemeDir()."/javascript/DataTables.js");
		if ($table = DataTable::get()->byID($args['id'])) return $table->forTemplate();
	}
	
	public function CssPadding()
	{
		if ($this->CellPadding)
		{
			if (preg_match("/.\s./",$this->CellPadding)) 
			{
				// format is XXpx YYpx
				return $this->CellPadding;
			}
			elseif (preg_match("/[px|\%]/",$this->CellPadding))
			{
				// format is either XXpx or XX%
				return $this->CellPadding;
			}
			// no format, just a number
			return preg_replace("/[^0-9]/","",$this->CellPadding).'px';
		}
	}
	
	public function ColumnCount()
	{
		if ( ($this->FirstRowHeader) && ($this->HeaderRow()) )
		{
			return $this->HeaderRow()->DataTableColumns()->Count();
		}
		$count = 0;
		foreach($this->DataTableRows() as $row)
		{
			$count = ($row->DataTableColumns()->Count() > $count) ? $row->DataTableColumns()->Count() : $count;
		}
		return $count;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		$parentRow = null;
		foreach($this->DataTableRows() as $row)
		{
			$row->MatchRows();
		}
	}
	
	public function forTemplate()
	{
		return $this->renderWith('DataTable');
	}
	
	public function HeaderRow()
	{
		return $this->DataTableRows()->exclude('SortOrder',0)->First();
	}
	
	public function BodyRows()
	{
		$headerRow = $this->HeaderRow();
		$columnCount = $this->ColumnCount();
		$bodyRows = new ArrayList();
		foreach($this->DataTableRows() as $row)
		{
			if ($headerRow && ($headerRow->ID == $row->ID)) continue;
			$bodyColumns = new ArrayList();
			$cur_count = 0;
			foreach($row->DataTableColumns() as $col)
			{
				$cur_count++;
				if ($cur_count <= $columnCount)
				{
					$bodyColumns->push($col);
				}
			}
			// make sure we have enough columns
			while($bodyColumns->Count() < $columnCount)
			{
				$bodyColumns->push( new DataTableColumn() );
			}
			$bodyRows->push( new ArrayData( array('BodyColumns' => $bodyColumns) ) );			
		}
		return $bodyRows;
	}
}



