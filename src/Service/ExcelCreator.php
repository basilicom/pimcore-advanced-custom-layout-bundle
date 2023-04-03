<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Service;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Pimcore\Model\DataObject\Concrete;

class ExcelCreator
{
    private const VISIBLE_AND_EDITABLE = 'Visible & Editable';
    private const VISIBLE_AND_NOT_EDITABLE = 'Visible & Not Editable';
    private const INVISIBLE = 'Invisible';

    private string $folderName = PIMCORE_PROJECT_ROOT . '/var/bundles/AdvancedCustomLayouts';
    private ConfigurationService $configurationService;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @throws Exception
     */
    public function create(): void
    {
        @mkdir($this->folderName, 0777, true);

        $configurations = $this->configurationService->getCustomLayoutConfigs();
        $excelFileName = $this->folderName . '/CustomLayouts.xlsx';

        /** @var CustomLayoutConfig[][] $sheetDataset */
        $sheetDataset = [];
        foreach ($configurations as $configuration) {
            $class = $configuration->getFullQualifiedClassName();
            if (!class_exists($class)) {
                throw new Exception(sprintf('Class %s does not exist', $class));
            }

            $sheetDataset[$class][] = $configuration;
        }

        $spreadsheet = new Spreadsheet();
        $index = 0;
        foreach ($sheetDataset as $className => $configurations) {
            /** @var Concrete $object */
            $object = new $className();
            $classNameParts = explode('\\', $className);
            $classShortName = end($classNameParts);

            $worksheet = $index > 0 ? $spreadsheet->createSheet($index) : $spreadsheet->getActiveSheet();
            $worksheet->setTitle($classShortName);
            $worksheet->setCellValue('A1', $classShortName);
            $worksheet->getStyle('A1:Z1')->getFont()->setBold(true);
            $worksheet->getStyle('A1:Z1')->getFont()->setSize(16);
            $worksheet->getStyle('A1:Z1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
            $worksheet->getColumnDimensionByColumn(1)->setWidth(50);
            $worksheet->getColumnDimensionByColumn(2)->setWidth(5);

            $columns = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $row = 1;
            foreach ($configurations as $index => $configuration) {
                $worksheet->getColumnDimensionByColumn($index + 3)->setAutoSize(true);
                $column = substr($columns, $index + 2, 1);
                $worksheet->setCellValue($column . $row, $configuration->getLabel());
            }

            $row = 2;
            foreach ($object->getClass()->getFieldDefinitions() as $fieldDefinition) {
                $fieldName = $fieldDefinition->getName();
                $worksheet->setCellValue('A' . $row, $fieldName);

                // add column per layout
                foreach ($configurations as $index => $configuration) {
                    $column = substr($columns, $index + 2, 1);
                    $this->addSelectBox($worksheet, $column . $row);

                    $isShowMode = $configuration->getMode() === CustomLayoutConfig::MODE_SHOW;
                    if ($isShowMode) {
                        $worksheet->setCellValue($column . $row, self::INVISIBLE);
                    } else {
                        if ($fieldDefinition->getInvisible()) {
                            $defaultValue = self::INVISIBLE;
                        } else {
                            $defaultValue = $fieldDefinition->getNoteditable()
                                ? self::VISIBLE_AND_NOT_EDITABLE
                                : self::VISIBLE_AND_EDITABLE;
                        }

                        $worksheet->setCellValue($column . $row, $defaultValue);
                    }

                    foreach ($configuration->getFields() as $field) {
                        if ($field->getFieldId() === $fieldName) {
                            $isVisible = $isShowMode || ($field->getIsVisible() ?? !$fieldDefinition->getInvisible());
                            if ($isVisible) {
                                $isEditable = $field->getIsEditable() ?? !$fieldDefinition->getNoteditable();
                                if ($isEditable) {
                                    $worksheet->setCellValue($column . $row, self::VISIBLE_AND_EDITABLE);
                                } else {
                                    $worksheet->setCellValue($column . $row, self::VISIBLE_AND_NOT_EDITABLE);
                                }
                            } else {
                                $worksheet->setCellValue($column . $row, self::INVISIBLE);
                            }

                            break;
                        }
                    }
                }
                $row++;
            }
            $index++;
        }

        // Create a new Excel writer and save the file
        $writer = new Xlsx($spreadsheet);
        $writer->save($excelFileName);
    }

    protected function addSelectBox(Worksheet $worksheet, string $cellName): void
    {
        $objValidation = $worksheet->getCell($cellName)->getDataValidation();
        $objValidation->setType(DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setFormula1(
            '"' . implode(',', [self::VISIBLE_AND_EDITABLE, self::INVISIBLE, self::VISIBLE_AND_NOT_EDITABLE]) . '"'
        );
    }
}
