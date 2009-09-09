<?php
// autoload the mvc
if (t3lib_extMgm::isLoaded ( 'mvc' )) {
	require_once (t3lib_extMgm::extPath ( 'mvc' ) . 'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll ();
} else {
	exit ( 'Framework "mvc" not loaded!' );
}

require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php');
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php');

require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/language/class.tx_l10nmgr_models_language_language.php');
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/language/class.tx_l10nmgr_models_language_languageRepository.php');

require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'interface/interface.tx_l10nmgr_interface_wordsCountable.php');

require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php');
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php');
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_translateableField.php');


require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider.php');

require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');

/**
 * This class is used to test the functionallity of the l10nAccumulatedInformationsFactory class.
 *
 * @author Timo Schmidt
 * @see tx_l10nmgr:l10nAccumulatedInformationFactory
 *
 */

class tx_l10nmgr_l10nTranslatedInformationFactory_testcase extends tx_phpunit_database_testcase {

	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp() {
		$this->createDatabase ();
		$db = $this->useTestDatabase ();
		$this->importStdDB();
		
		$this->importExtensions ( array ('cms', 'l10nmgr', 'static_info_tables', 'templavoila' ) );
	}

	public function tearDown() {
		$GLOBALS ['TYPO3_DB']->sql_select_db ( TYPO3_db );
	}

	/**
	 * Method to ensure that the fixtureL10NConfig can be loaded from the testdatabase.
	 *
	 * @param void
	 * @return void
	 *
	 */
	public function test_canLoadFixtureL10NConfig() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );

		$fixtureConfig = $this->getFixtureL10NConfig ();

		$this->assertEquals ( $fixtureConfig->getId (), 4711, 'Fixture l10nConfig can not be loaded' );

	}

	/**
	 * Testcase to ensure that the fixtureTargetLanguage can be loaded from the test database.
	 *
	 * @param void
	 * @return void
	 */
	public function test_canLoadFixtureTargetLanguage() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$fixtureTargetLanguage = $this->getFixtureTargetLanguage ();
		$this->assertEquals ( $fixtureTargetLanguage->getUid(), 999, 'Fixture Targetlanguage can not be loaded' );
	}

	/**
	 * Testcase to ensuse that the fixturePreviewLanguage can be loaded from the test database.
	 *
	 * @param void
	 * @return void
	 *
	 */
	public function test_canLoadFixturePreviewLanguage() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$fixturePreviewLanguage = $this->getFixturePreviewLanguage ();

		$this->assertEquals ( $fixturePreviewLanguage->getUid(), 998, 'Fixture Previewlanguage can not be loaded' );
	}

	/**
	 * This testcase should ensure that the TranslateableInformationsFactory creates a
	 * translateableInformation with the correct pageGroups
	 *
	 * @param void
	 * @return void
	 */
	public function test_canCreateTranslateableInformationForPageId() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canCreateTranslateableInformationsForPageId.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$fixtureLimitToPageIds = $this->getFixtureLimitToPageids ();

		$ids = array ();
		$factory = new tx_l10nmgr_models_translateable_translateableInformationFactory ( );

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$typo3DataProvider			=  new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData, $fixtureLimitToPageIds );
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );

		$pageGroups = $translateableInformations->getPageGroups ();

		foreach ( $pageGroups as $pageGroup ) {
			$ids [$pageGroup->getUid ()] = ( int ) $pageGroup->getUid ();
		}

		$this->assertTrue ( in_array ( 4711, $ids, 'translatedable page could not be found in pageid array' ) );
		$this->assertTrue ( in_array ( 4712, $ids, 'translatedable page could not be found in pageid array' ) );
		$this->assertFalse ( in_array ( 4713, $ids, 'page not in limit found in translateable pages' ) );
	}

	public function test_canDetermineTranslateableElementsForPageIds() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canDetermineTranslateableElementsForPageIds.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);
		$fixtureLimitToPageIds = $this->getFixtureLimitToPageids ();

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			=  new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData, $fixtureLimitToPageIds);
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );

		$pageGroups = $translateableInformations->getPageGroups ();
		$firstField = $pageGroups->offsetGet ( 0 )->getTranslateableElements ()->offsetGet ( 0 )->getTranslateableFields ()->offsetGet ( 0 );

		$this->assertEquals ( $firstField->getIdentityKey (), 'pages_language_overlay:NEW/999/4711:title' );
		$this->assertEquals ( 1, $firstField->countWords () );

		$wordCountOfFirstContentElement = $pageGroups->offsetGet ( 0 )->getTranslateableElements ()->offsetGet ( 1 )->countWords ();
		$this->assertEquals ( $wordCountOfFirstContentElement, 7, 'Determined wrong word count' );
	}

	public function test_canGetContentElementFromPageAndReturnCorrectDiffToDefaut() {
		try{
			$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canGetContentElementFromPageAndReturnCorrectDiffToDefaut.xml' );
			$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
			$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
			$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
			$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

			$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
			$exportData = $exportDataRepository->findById(9999);

			$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
			$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(4711)));
			$translateableInformations 	= $factory->createFromDataProvider( $typo3DataProvider );

			$pageGroups = $translateableInformations->getPageGroups ();
			$allElements = $pageGroups->offsetGet ( 0 )->getTranslateableElements ();

			//get second element (first is the page itself).
			$ttContentElement = $allElements->offsetGet ( 1 );

			$this->assertEquals(1,$ttContentElement->getTranslateableFields()->count(),'Unexpected number of translateableFields');

			$headerField = $ttContentElement->getTranslateableFields()->offsetGet(0);

			$this->assertEquals('l18n',$headerField->getDiffDefaultValue(),'Incorrect diffDefaultValue');
		}catch(Exception $e){

			 $e->getTrace();

		}
	}

	public function test_determineCorrectTranslateableFields() {
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/determineCorrectTranslateableFields.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(4711)));
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );


		$pageGroups = $translateableInformations->getPageGroups ();

		$allElements = $pageGroups->offsetGet ( 0 )->getTranslateableElements ();

		//get second element (first is the page itself).
		$ttContentElement = $allElements->offsetGet ( 1 );

		/**
		 * should contain header,headerlink and bodytext. The field column is localized but should
		 * not appear as translateableField because it is an integer.
		 */
		$this->assertEquals(3,$ttContentElement->getTranslateableFields()->count(),'Unexpected number of translateableFields');
		$translateableFields = $ttContentElement->getTranslateableFields();

		$headerField 		=	$translateableFields->offsetGet(0);
		$headerLinkField 	=	$translateableFields->offsetGet(1);
		$bodytextField 		=	$translateableFields->offsetGet(2);


		$this->assertTrue($headerField->isChanged(),'The header should appear as changed and therefore it needs to be translated again.');
		$this->assertFalse($headerLinkField->isChanged(),'The header link should not be changed therefore no new translatio is needed.');
		$this->assertTrue($bodytextField->isChanged(),'The bodytext should appear as changed and therefore it needs to be translated again.');
	}


	public function test_canReturnCorrectDiffToDefaultForPage(){
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canReturnCorrectDiffToDefaultForPage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		//diff must be something like l18n
		$factory = new tx_l10nmgr_models_translateable_translateableInformationFactory();

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(99999)));
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );

		/**
		 * @todo: there is a bug in TYPO3. There is no correct l18n_diffsource for a new pages_language_overlay.
		 * When a new pages_language_overlay is created the content of the l18n_diffsource is an empty, serialized array
		 */
	}

	public function test_canGetDiffToDefaultFromLanguageInheritanceFCE(){
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canGetDiffToDefaultFromLanguageInheritanceFCE.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(9999)));
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );
		/**
		 * The TranslateableInformation has 2 translateableElements:
		 *
		 * The first one is the page, the second is the FCE.  The FCE has two fields, a header and a content field.
		 * The Initial Value of the Header was "Header", after creation it has been translated to UK. After the translation
		 * the original "Header" has been changed to "Header Changed". As diffDefault we expect "Header", because it was
		 * the value a the moment when the translation was started
		 *
		 */
		$pageGroups = $translateableInformations->getPageGroups ();

		$translateableElements 	= $pageGroups->offsetGet ( 0 )->getTranslateableElements ();
		$fceElement				= $translateableElements->offsetGet(1);

		$headerField			= $fceElement->getTranslateableFields()->offsetGet(0);

		$this->assertEquals("Header",$headerField->getDiffDefaultValue());
		$this->assertEquals("Header Translated",$headerField->getTranslationValue());
		$this->assertEquals("Header Changed",$headerField->getDefaultValue());


		$contentField			= $fceElement->getTranslateableFields()->offsetGet(1);
		$this->assertEquals("Content", $contentField->getDiffDefaultValue());
		$this->assertEquals("Content Translated",$contentField->getTranslationValue());
		$this->assertEquals("Content Changed",$contentField->getDefaultValue());

	}

	public function test_canGetDiffToDefaultFromLanguageSeparateFCE(){
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canGetDiffToDefaultFromLanguageSeparateFCE.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$factory = new tx_l10nmgr_models_translateable_translateableInformationFactory();

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(99999)));
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );


		/**
		 * The FCE on the page is configured with langChildren = 0 AND langDisable = 0. This means, that there
		 * is a sepperate translation and the translation is indepenedent from the original. Therefore the
		 * translateable Information should only contain a page and a contentelement. The contentelement should
		 * not have any translateable field.
		 */

		$pageGroups 			= $translateableInformations->getPageGroups ();
		$translateableElements 	= $pageGroups->offsetGet ( 0 )->getTranslateableElements ();
		$ttContentElement 		= $translateableElements ->offsetGet ( 1 );

		$this->assertEquals($ttContentElement->getTranslateableFields()->count(), 0,'Unexpected number of translateable fields');
	}

	public function test_canGetDiffToDefaultFromDatabaseTranslatedFCE(){
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canGetDiffToDefaultFromDatabaseTranslatedFCE.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixturePreviewLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureTargetLanguage.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureL10NConfig.xml' );
		$this->importDataSet ( dirname ( __FILE__ ) . '/fixtures/canLoadFixtureExportData.xml' );

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(9999);

		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory ( );
		$typo3DataProvider			= new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,  new ArrayObject(array(99999)));
		$translateableInformations 	= $factory->createFromDataProvider ( $typo3DataProvider );

		$pageGroups = $translateableInformations->getPageGroups ();

		$translateableElements 		= $pageGroups->offsetGet ( 0 )->getTranslateableElements ();
		$contentElement				= $translateableElements->offsetGet(1);

		$ContentElementHeaderField	= $contentElement->getTranslateableFields()->offsetGet(0);
		$FCEHeaderField				= $contentElement->getTranslateableFields()->offsetGet(1);
		$FCEContentField			= $contentElement->getTranslateableFields()->offsetGet(2);

		$this->assertEquals('Original', $ContentElementHeaderField->getDefaultValue());
		$this->assertEquals('Original', $ContentElementHeaderField->getTranslationValue());
		$this->assertFalse($ContentElementHeaderField->isChanged());

		$this->assertEquals('Header Changed',$FCEHeaderField->getDefaultValue());
		$this->assertEquals('Header Translation',$FCEHeaderField->getTranslationValue());
		$this->assertEquals('Header',$FCEHeaderField->getDiffDefaultValue());
		$this->assertTrue($FCEHeaderField->isChanged());

		$this->assertEquals('Content Changed',$FCEContentField->getDefaultValue());
		$this->assertEquals('Content Translation',$FCEContentField->getTranslationValue());
		$this->assertEquals('Content',$FCEContentField->getDiffDefaultValue());
		$this->assertTrue($FCEContentField->isChanged());
	}



	/**
	 * This method is used to load a FixtureL10NConfig
	 *
	 * @return tx_l10nmgr_models_configuration_configuration
	 */
	protected function getFixtureL10NConfig() {
		$fixtureConfigRepository = new tx_l10nmgr_models_configuration_configurationRepository ( );
		$fixtureConfig = $fixtureConfigRepository->findById ( 4711 );

		return $fixtureConfig;
	}

	/**
	 * This method loads an instance if a fixture Target Language
	 *
	 * @return tx_l10nmgr_l10nLanguage
	 */
	protected function getFixtureTargetLanguage() {
		$fixtureLanguageRepository = new tx_l10nmgr_models_language_languageRepository ( );
		$fixtureLanguage = $fixtureLanguageRepository->findById ( 999 );

		return $fixtureLanguage;
	}

	/**
	 * This method loads an instance on a fixture Preview Language
	 *
	 * @return tx_l10nmgr_l10nLanguage
	 */
	protected function getFixturePreviewLanguage() {
		$fixtureLanguageRepository = new tx_l10nmgr_models_language_languageRepository ( );
		$fixtureLanguage = $fixtureLanguageRepository->findById ( 998 );

		return $fixtureLanguage;
	}

	/**
	 * A list of Accumulated Informations can be limited to a set of pageIds (to limit the size of the resulting xml file)
	 * This method returns a fixtureCollection of pageIds that should be used as limit of pageIds
	 *
	 * @return ArrayObject
	 */
	protected function getFixtureLimitToPageids() {
		$limitPageIdCollection = new ArrayObject ( );
		$limitPageIdCollection->append ( 4711 );
		$limitPageIdCollection->append ( 4712 );

		return $limitPageIdCollection;

	}

}

?>