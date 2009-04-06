<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_exportStateRepository.php';



class tx_l10nmgr_exportData extends /* tx_mvc_ddd_abstractDbObject */ tx_mvc_ddd_typo3_abstractTCAObject {

	/**
	 * Initialize the database object with
	 * the table name of current object
	 *
	 * @access public
	 * @return string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_exportdata';
	}

	/**
	 * Get l10nConfiguration record
	 *
	 * @return tx_l10nmgr_l10nConfiguration
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getL10nConfiguration() {
		if (empty($this->row['l10ncfg_id'])) {
			throw new LogicException('No "l10ncfg_id" found!');
		}

		if (empty($this->row['l10nconfiguration'])) {
			$this->row['l10nconfiguration'] = new tx_l10nmgr_l10nConfiguration();
			$this->row['l10nconfiguration']->load($this->row['l10ncfg_id']);
		}
		return $this->row['l10nconfiguration'];
	}

	/**
	 * Get collection of tx_l10nmgr_exportState objects
	 *
	 * @return ArrayObject Collection of tx_l10nmgr_exportState objects
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getStatesCollection() {
		if (empty($this->row['uid'])) {
			throw new LogicException('No "uid" found!');
		}

		if (empty($this->row['statescollection'])) {
			$statesRepository = new tx_l10nmgr_exportStateRepository();
			$this->row['statescollection'] = $statesRepository->findByexportdata_id($this->row['uid']);
		}
		return $this->row['statescollection'];
	}

	/**
	 * Get collection of tx_l10nmgr_exportFile objects
	 *
	 * @return ArrayObject Collection of tx_l10nmgr_exportFile objects
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getFiles() {

	}

}

?>