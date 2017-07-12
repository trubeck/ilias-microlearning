<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* This class represents a text property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id: class.ilMatchMemoThemeInputGUI.php 22272 2009-11-03 09:15:47Z hschottm $
* @ingroup	ServicesForm
*/
class ilMatchMemoThemeInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $themes;
	protected $enabled;
	protected $plugin;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->enabled = true;
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MatchMemo");
	}
	
	public function setThemes($a_themes)
	{
		$this->themes = $a_themes;
	}
	
	public function getThemes()
	{
		return $this->themes;
	}

	public function setEnabled($a_enabled)
	{
		$this->enabled = $a_enabled;
	}
	
	public function getEnabled()
	{
		return $this->enabled;
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		if (is_array($a_value))
		{
			$this->themes = array();
			$this->plugin->includeClass("class.ilMatchMemoTheme.php");
			foreach ((array)$a_value['title'] as $idx => $title)
			{
				$theme = new ilMatchMemoTheme($title, $a_value['easy'][$idx], $a_value['medium'][$idx], $a_value['hard'][$idx]);
				$theme->mixedpools = $a_value['rows'][$idx];
				foreach ((array)$a_value['mixed'][$idx] as $counter => $selection)
				{
					if ($selection > 0) $theme->addMixedPool($selection, $_POST['themes']['mixed_percent'][$idx][$counter]);
				}
				array_push($this->themes, $theme);
			}
		}
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->themes;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		if (is_array($_POST[$this->getPostVar()]['title']))
		{
			foreach ($_POST[$this->getPostVar()]['title'] as $idx => $title)
			{
				if ($this->getRequired() && strlen($title) == 0) {
					$this->setAlert($this->plugin->txt("msg_input_is_required"));
					return false;
				}
				if (($_POST[$this->getPostVar()]['easy'][$idx] == 0) && ($_POST[$this->getPostVar()]['medium'][$idx] == 0) && ($_POST[$this->getPostVar()]['hard'][$idx] == 0))
				{
					$this->setAlert($this->plugin->txt("msg_topic_is_required"));
					return false;
				}
				foreach ($_POST[$this->getPostVar()]['mixed'] as $themeidx => $mixedpools)
				{
					$req_number_of_pools            = max(2, (int)$_POST[$this->getPostVar()]['rows'][$themeidx]);
					$number_of_desired_pools        = count(array_filter(array_map('intval', $mixedpools)));
					$number_of_unique_desired_pools = count(array_unique(array_filter(array_map('intval', $mixedpools))));

					if($number_of_desired_pools > 0 && $number_of_desired_pools > $number_of_unique_desired_pools)
					{
						$this->setAlert(sprintf($this->plugin->txt("msg_min_different_pools_violation"), $req_number_of_pools));
						return false;
					}

					$totalpercentage = 0.0;
					$valid_pools     = array();
					$all_empty       = true;

					foreach($mixedpools as $poolidx => $obj_fi)
					{
						if(!$obj_fi)
						{
							continue;
						}

						$pecentage = trim($_POST[$this->getPostVar()]['mixed_percent'][$themeidx][$poolidx]);
						if(strlen($pecentage))
						{
							$all_empty = false;
							break;
						}
					}

					foreach($mixedpools as $poolidx => $obj_fi)
					{
						if(!$obj_fi)
						{
							continue;
						}

						$pecentage        = trim($_POST[$this->getPostVar()]['mixed_percent'][$themeidx][$poolidx]);
						if(!$all_empty && !strlen($pecentage))
						{
							$this->setAlert($this->plugin->txt("msg_wrong_percentage"));
							return false;
						}

						if(strlen($pecentage) && ($pecentage < 1 || $pecentage > 99 || !ctype_digit($pecentage)))
						{
							$this->setAlert($this->plugin->txt("msg_wrong_percentage_range"));
							return false;
						}

						$totalpercentage += $pecentage;
						$valid_pools[$poolidx] = $poolidx;
					}

					if($number_of_desired_pools > 0 && count($valid_pools) < $req_number_of_pools)
					{
						$this->setAlert(sprintf($this->plugin->txt("msg_min_different_pools_violation"), $req_number_of_pools));
						return false;
					}
					
					if($totalpercentage > 0 && $totalpercentage != 100.0)
					{
						$this->setAlert($this->plugin->txt("msg_wrong_percentage"));
						return false;
					}
				}
			}
		}
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$template = $this->plugin->getTemplate("tpl.prop_themeinput.html");
		$i = 0;
		foreach ($this->themes as $theme)
		{
			if (strlen($theme->title))
			{
				$template->setCurrentBlock("prop_value");
				$template->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($theme->title));
				$template->parseCurrentBlock();
			}

			$pools = $theme->getAvailablePools();
			if(count($pools))
			{
				$pools[0] = $this->plugin->txt('obj_xmpl_select');
			}
			foreach ($pools as $obj_id => $pool)
			{
				$template->setCurrentBlock("option_easy");
				$template->setVariable("OPTION_VALUE", $obj_id);
				$template->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($pool));
				if ($obj_id == $theme->easy) $template->setVariable("SELECTED_EASY", ' selected="selected"');
				$template->parseCurrentBlock();
				$template->setCurrentBlock("option_medium");
				$template->setVariable("OPTION_VALUE", $obj_id);
				$template->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($pool));
				if ($obj_id == $theme->medium) $template->setVariable("SELECTED_MEDIUM", ' selected="selected"');
				$template->parseCurrentBlock();
				$template->setCurrentBlock("option_hard");
				$template->setVariable("OPTION_VALUE", $obj_id);
				$template->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($pool));
				if ($obj_id == $theme->hard) $template->setVariable("SELECTED_HARD", ' selected="selected"');
				$template->parseCurrentBlock();
			}
			
			if ($this->getEnabled())
			{
				$template->setCurrentBlock('buttons');
				$template->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
				$template->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
				$template->setVariable("BUTTON_POST_VAR", $this->getPostVar());
				$template->setVariable("BUTTON_ROW_INDEX", $i);
				$template->parseCurrentBlock();
			}
			
			// mixed pool selection
			for ($j = 0; $j < $theme->mixedpools; $j++)
			{
				$pools = $theme->getAvailablePools();
				if(count($pools))
				{
					$pools[0] = $this->plugin->txt('obj_xmpl_select');
				}
				foreach($pools as $obj_id => $pool)
				{
					$template->setCurrentBlock("option_mixed");
					$template->setVariable("OPTION_VALUE", $obj_id);
					$template->setVariable("OPTION_TEXT", ilUtil::prepareFormOutput($pool));
					if ($obj_id == $theme->mixed[$j]['obj_id']) $template->setVariable("SELECTED_MIXED", ' selected="selected"');
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock('pool_mixed');
				$template->setVariable("POST_VAR", $this->getPostVar());
				$template->setVariable("ROW_INDEX", $i);
				$template->setVariable("COUNTER", $j);
				if (!$this->getEnabled())
				{
					$template->setVariable("DISABLED_SELECT_MIXED", ' disabled="disabled"');
					$template->setVariable("DISABLED_MIXED_PERCENT", ' disabled="disabled"');
				}
				if ($theme->mixed[$j]['percent'] > 0) $template->setVariable("MIXED", ' value="' . ilUtil::prepareFormOutput($theme->mixed[$j]['percent']) . '"');
				$template->parseCurrentBlock();
			}

			for ($k = 2; $k <= 20; $k++)
			{
				$template->setCurrentBlock('row_option');
				$template->setVariable("ROW_OPTION_VALUE", $k);
				$template->setVariable("ROW_OPTION_TEXT", $k);
				if ($k == $theme->mixedpools)
				{
					$template->setVariable("ROW_OPTION_VALUE_SELECTED", ' selected="selected"');
				}
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock('theme');
			if (!$this->getEnabled())
			{
				$template->setVariable("DISABLED_TITLE", ' disabled="disabled"');
				$template->setVariable("DISABLED_SELECT", ' disabled="disabled"');
				$template->setVariable("DISABLED_SETROWS", ' disabled="disabled"');
				$template->setVariable("DISABLED_MIXEDSELECTOR", ' disabled="disabled"');
			}
			$template->setVariable("ROW_LABEL", $this->plugin->txt('row_label'));
			$template->setVariable("TEXT_SELECT", $lng->txt('select'));
			$template->setVariable("TEXT_TOPIC", $this->plugin->txt('topic'));
			$template->setVariable("POST_VAR", $this->getPostVar());
			$template->setVariable("ROW_INDEX", $i);
			$template->setVariable('PLEASE_SELECT', $this->plugin->txt('please_select'));
			$template->setVariable('TEXT_EASY', $this->plugin->txt('theme_easy'));
			$template->setVariable('TEXT_MEDIUM', $this->plugin->txt('theme_medium'));
			$template->setVariable('TEXT_HARD', $this->plugin->txt('theme_hard'));
			$template->setVariable('TEXT_MIXED', $this->plugin->txt('theme_mixed'));
			if(!$this->getEnabled())
			{
				$template->setVariable('DISABLED_TABLE', ' table-disabled');
			}
			$template->parseCurrentBlock();

			$i++;
		}
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $template->get());
		$a_tpl->parseCurrentBlock();
	}
}
