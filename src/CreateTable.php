<?php
/**
 * @author: 秋田嘉
 * @email: 997348985@qq.com
 * @fileName CreateTable.php
 * @date: 2018/7/6 10:32
 * @describe: TODO
 */

namespace QiuTianJia\MySQLDDIC;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use QiuTianJia\MySQLDDIC\Tools\PDOSingleton;

class CreateTable
{
    private $pdo;

    private $spreadSheet;

    private $activeTableListSheet;

    private $activeTableListIndexSheet;

    private $tableListHyperlinkArray = [];

    private $activeVersionHistoryListSheet;

    private $activeViewListSheet;

    private $activeTriggerListSheet;

    public $config;

    public $columnStyle;

    public $columnHeaderStyle;

    public $tableHeaderStyle;

    public $tableStyle;


    public function __construct()
    {
        $this->config = include __DIR__ . DIRECTORY_SEPARATOR . "Config.php";
        $this->pdo = PDOSingleton::getInstance($this->config['database']['dsn'], $this->config['database']['username'], $this->config['database']['password'], $this->config['database']['driver_options']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->loadExcelConfig()
            ->loadTableListSheetConfig()
            ->versionHistoryListWorksheet()
            ->tableListWorksheet()
            ->tableListIndexWorksheet()
            ->viewListWorksheet()
            ->triggerListWorksheet()
            ->procedureListWorksheet()
            ->generate();
    }

    public function loadExcelConfig()
    {
        $this->spreadSheet = new Spreadsheet();
        $this->spreadSheet->getDefaultStyle()->getFont()
            ->setName($this->config['excel']['font_name'])
            ->setSize($this->config['excel']['font_size']);
        $this->spreadSheet->getDefaultStyle()->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $this->spreadSheet->setActiveSheetIndex(0);

        $this->spreadSheet->getProperties()
            ->setCategory($this->config['excel']['category'])
            ->setCreator($this->config['excel']['creator'])
            ->setTitle($this->config['excel']['title'])
            ->setKeywords($this->config['excel']['keywords'])
            ->setDescription($this->config['excel']['description'])
            ->setCreated($this->config['excel']['created'])
            ->setLastModifiedBy($this->config['excel']['last_modified_by']);

        $this->columnHeaderStyle = [
            'font' => [
                'color' => [
                    'argb' => Color::COLOR_WHITE,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => '538ED5',
                ],
            ],
        ];

        $this->columnStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $this->tableHeaderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'font' => [
                'color' => [
                    'argb' => Color::COLOR_WHITE,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => '60497B',
                ],
            ],
        ];

        $this->tableStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        return $this;
    }

    public function loadTableListSheetConfig()
    {
        $this->activeTableListSheet = $this->spreadSheet->getActiveSheet();
        $this->activeTableListSheet->setTitle($this->config['excel']['worksheet']['table']['list']['title']);
        $this->activeTableListSheet->getColumnDimension('B')->setWidth(10);
        $this->activeTableListSheet->getColumnDimension('C')->setWidth(20);
        $this->activeTableListSheet->getColumnDimension('D')->setWidth(25);
        $this->activeTableListSheet->getColumnDimension('E')->setWidth(30);
        $this->activeTableListSheet->getColumnDimension('F')->setWidth(15);
        $this->activeTableListSheet->getColumnDimension('G')->setWidth(15);
        $this->activeTableListSheet->getColumnDimension('H')->setWidth(20);
        $this->activeTableListSheet->getColumnDimension('I')->setWidth(35);
        $this->activeTableListSheet->getDefaultRowDimension()->setRowHeight(20);

        return $this;
    }

    public function tableListWorksheet()
    {
        $tablePropertiesPDOStatement = $this->pdo->query('SHOW TABLE STATUS');

        $tableRows = $rows = 1;
        $rows++;
        while ($tableProperties = $tablePropertiesPDOStatement->fetch(\PDO::FETCH_OBJ)) {
            $this->activeTableListSheet->mergeCells('B' . ($rows - 1) . ':I' . ($rows - 1));
            $this->activeTableListSheet->setCellValue('B' . $rows, '表' . '(' . $tableRows . ')' . $tableProperties->Name . ' ' . $tableProperties->Comment);
            $this->activeTableListSheet->mergeCells('B' . $rows . ':I' . $rows);

            $this->tableListHyperlinkArray[] = [
                'Hyperlink' => 'B' . $rows,
                'TableData' => [
                    'Name' => $tableProperties->Name,
                    'Comment' => $tableProperties->Comment,
                ]
            ];

            $columnBorderRows = $rows;
            $rows++;

            $this->activeTableListSheet->setCellValue('B' . $rows, '序号');
            $this->activeTableListSheet->setCellValue('C' . $rows, '字段名');
            $this->activeTableListSheet->setCellValue('D' . $rows, '类型（长度）');
            $this->activeTableListSheet->setCellValue('E' . $rows, '字段默认值');
            $this->activeTableListSheet->setCellValue('F' . $rows, 'Null');
            $this->activeTableListSheet->setCellValue('G' . $rows, 'Key');
            $this->activeTableListSheet->setCellValue('H' . $rows, '其它');
            $this->activeTableListSheet->setCellValue('I' . $rows, '备注');
            $this->activeTableListSheet->getStyle('B' . $rows . ':I' . $rows)->applyFromArray($this->columnHeaderStyle);

            $rows++;
            $columnPropertiesPDOStatement = $this->pdo->query('SHOW FULL COLUMNS FROM ' . $tableProperties->Name);
            $columnRecordRows = 1;
            while ($columnProperties = $columnPropertiesPDOStatement->fetch(\PDO::FETCH_OBJ)) {
                $this->activeTableListSheet->setCellValue('B' . $rows, $columnRecordRows);
                $this->activeTableListSheet->setCellValue('C' . $rows, $columnProperties->Field);
                $this->activeTableListSheet->setCellValue('D' . $rows, $columnProperties->Type);
                $this->activeTableListSheet->setCellValue('E' . $rows, $columnProperties->Default);
                $this->activeTableListSheet->setCellValue('F' . $rows, $columnProperties->Null);
                $this->activeTableListSheet->setCellValue('G' . $rows, $columnProperties->Key);
                $this->activeTableListSheet->setCellValue('H' . $rows, $columnProperties->Extra);
                $this->activeTableListSheet->setCellValue('I' . $rows, $columnProperties->Comment);
                $this->activeTableListSheet->getStyle('B' . $columnBorderRows . ':I' . $rows)->applyFromArray($this->columnStyle);
                unset($columnProperties);
                $columnRecordRows++;
                $rows++;
            }
            unset($tableProperties);
            $tableRows++;
            $rows++;
        }

        return $this;
    }

    public function tableListIndexWorksheet()
    {
        if (empty($this->tableListHyperlinkArray)) {
            return false;
        }

        $this->activeTableListIndexSheet = $this->spreadSheet->createSheet();
        $this->activeTableListIndexSheet->setTitle($this->config['excel']['worksheet']['table']['index']['title']);
        $this->activeTableListIndexSheet->getColumnDimension('B')->setWidth(10);
        $this->activeTableListIndexSheet->getColumnDimension('C')->setWidth(20);
        $this->activeTableListIndexSheet->getColumnDimension('D')->setWidth(25);
        $this->activeTableListIndexSheet->getColumnDimension('E')->setWidth(30);
        $this->activeTableListIndexSheet->getColumnDimension('F')->setWidth(15);
        $this->activeTableListIndexSheet->getColumnDimension('G')->setWidth(15);
        $this->activeTableListIndexSheet->getColumnDimension('H')->setWidth(20);
        $this->activeTableListIndexSheet->getColumnDimension('I')->setWidth(35);
        $this->activeTableListIndexSheet->getDefaultRowDimension()->setRowHeight(20);

        $rows = 2;
        $this->activeTableListIndexSheet->mergeCells('B' . ($rows - 1) . ':I' . ($rows - 1));
        $this->activeTableListIndexSheet->setCellValue('B' . $rows, $this->config['excel']['worksheet']['table']['index']['title']);
        $this->activeTableListIndexSheet->mergeCells('B' . $rows . ':I' . $rows);
        $this->activeTableListIndexSheet->getStyle('B' . $rows . ':I' . $rows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ]);

        $rows++;
        $this->activeTableListIndexSheet->setCellValue('B' . $rows, '序号');
        $this->activeTableListIndexSheet->setCellValue('C' . $rows, '中文名');
        $this->activeTableListIndexSheet->setCellValue('D' . $rows, '');
        $this->activeTableListIndexSheet->setCellValue('E' . $rows, '物理名');
        $this->activeTableListIndexSheet->setCellValue('F' . $rows, '');
        $this->activeTableListIndexSheet->setCellValue('G' . $rows, '备注');
        $this->activeTableListIndexSheet->setCellValue('H' . $rows, '');
        $this->activeTableListIndexSheet->setCellValue('I' . $rows, '');
        $this->activeTableListIndexSheet->mergeCells('C' . $rows . ':D' . $rows);
        $this->activeTableListIndexSheet->mergeCells('E' . $rows . ':F' . $rows);
        $this->activeTableListIndexSheet->mergeCells('g' . $rows . ':I' . $rows);
        $this->activeTableListIndexSheet->getStyle('B' . $rows . ':I' . $rows)->applyFromArray($this->tableHeaderStyle);

        $rows++;
        foreach ($this->tableListHyperlinkArray as $key => &$value) {
            $this->activeTableListIndexSheet->setCellValue('B' . $rows, $key + 1);
            $this->activeTableListIndexSheet->setCellValue('C' . $rows, $value['TableData']['Comment']);
            $this->activeTableListIndexSheet->setCellValue('E' . $rows, $value['TableData']['Name']);
            $this->activeTableListIndexSheet->setCellValue('F' . $rows, '');
            $this->activeTableListIndexSheet->mergeCells('C' . $rows . ':D' . $rows);
            $this->activeTableListIndexSheet->mergeCells('E' . $rows . ':F' . $rows);
            $this->activeTableListIndexSheet->mergeCells('g' . $rows . ':I' . $rows);
            $this->activeTableListIndexSheet->getStyle('E' . $rows)->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
            $this->activeTableListIndexSheet->getStyle('B' . $rows . ':I' . $rows)->applyFromArray($this->tableStyle);
            $this->activeTableListIndexSheet->getCell('E' . $rows)->getHyperlink()->setUrl('sheet://' . $this->config['excel']['worksheet']['table']['list']['title'] . '!' . $value['Hyperlink']);

            $rows++;
        }

        return $this;
    }

    public function versionHistoryListWorksheet()
    {
        $this->activeVersionHistoryListSheet = $this->spreadSheet->createSheet();
        $this->activeVersionHistoryListSheet->setTitle($this->config['excel']['worksheet']['version_history']['list']['title']);
        $this->activeVersionHistoryListSheet->getColumnDimension('B')->setWidth(15);
        $this->activeVersionHistoryListSheet->getColumnDimension('C')->setWidth(15);
        $this->activeVersionHistoryListSheet->getColumnDimension('D')->setWidth(50);
        $this->activeVersionHistoryListSheet->getColumnDimension('E')->setWidth(30);
        $this->activeVersionHistoryListSheet->getDefaultRowDimension()->setRowHeight(20);

        $rows = 2;
        $this->activeVersionHistoryListSheet->setCellValue('B' . $rows, '版本历史');
        $this->activeVersionHistoryListSheet->mergeCells('B' . $rows . ':E' . $rows);
        $this->activeVersionHistoryListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ]);

        $rows++;
        $this->activeVersionHistoryListSheet->setCellValue('B' . $rows, '序号');
        $this->activeVersionHistoryListSheet->setCellValue('C' . $rows, '日期');
        $this->activeVersionHistoryListSheet->setCellValue('D' . $rows, '说明');
        $this->activeVersionHistoryListSheet->setCellValue('E' . $rows, '作者');
        $this->activeVersionHistoryListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray($this->tableHeaderStyle);
        $rows++;
        $this->activeVersionHistoryListSheet->setCellValue('B' . $rows, 1);
        $this->activeVersionHistoryListSheet->setCellValue('C' . $rows, date('Y/n/j', time()));

        $this->activeVersionHistoryListSheet->setCellValue('D' . $rows, '创建文档');
        $this->activeVersionHistoryListSheet->setCellValue('E' . $rows, $this->config['excel']['creator']);
        $this->activeVersionHistoryListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray($this->tableStyle);
        $rows++;
        while ($rows <= 23) {
            $this->activeVersionHistoryListSheet->setCellValue('B' . $rows, $rows - 3);
            $this->activeVersionHistoryListSheet->setCellValue('C' . $rows, '');
            $this->activeVersionHistoryListSheet->setCellValue('D' . $rows, '');
            $this->activeVersionHistoryListSheet->setCellValue('E' . $rows, '');
            $this->activeVersionHistoryListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray($this->tableStyle);
            $rows++;
        }

        return $this;
    }

    public function viewListWorksheet()
    {
        $this->activeViewListSheet = $this->spreadSheet->createSheet();
        $this->activeViewListSheet->setTitle($this->config['excel']['worksheet']['view']['list']['title']);
        $this->activeViewListSheet->getColumnDimension('B')->setWidth(15);
        $this->activeViewListSheet->getColumnDimension('C')->setWidth(15);
        $this->activeViewListSheet->getColumnDimension('D')->setWidth(50);
        $this->activeViewListSheet->getColumnDimension('E')->setWidth(30);
        $this->activeViewListSheet->getDefaultRowDimension()->setRowHeight(20);

        $rows = 2;
        $this->activeViewListSheet->setCellValue('B' . $rows, '视图');
        $this->activeViewListSheet->mergeCells('B' . $rows . ':E' . $rows);
        $this->activeViewListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ]);

        $rows++;
        $this->activeViewListSheet->setCellValue('B' . $rows, '序号');
        $this->activeViewListSheet->setCellValue('C' . $rows, '中文名');
        $this->activeViewListSheet->setCellValue('D' . $rows, '物理名');
        $this->activeViewListSheet->setCellValue('E' . $rows, '备注');
        $this->activeViewListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray($this->tableHeaderStyle);
        $rows++;

        while ($rows <= 23) {
            $this->activeViewListSheet->setCellValue('B' . $rows, $rows - 3);
            $this->activeViewListSheet->setCellValue('C' . $rows, '');
            $this->activeViewListSheet->setCellValue('D' . $rows, '');
            $this->activeViewListSheet->setCellValue('E' . $rows, '');
            $this->activeViewListSheet->getStyle('B' . $rows . ':E' . $rows)->applyFromArray($this->tableStyle);
            $rows++;
        }

        return $this;
    }

    public function triggerListWorksheet()
    {
        $this->activeTriggerListSheet = $this->spreadSheet->createSheet();
        $this->activeTriggerListSheet->setTitle($this->config['excel']['worksheet']['trigger']['list']['title']);
        $this->activeTriggerListSheet->getColumnDimension('B')->setWidth(15);
        $this->activeTriggerListSheet->getColumnDimension('C')->setWidth(15);
        $this->activeTriggerListSheet->getColumnDimension('D')->setWidth(50);
        $this->activeTriggerListSheet->getColumnDimension('E')->setWidth(30);
        $this->activeTriggerListSheet->getColumnDimension('F')->setWidth(30);
        $this->activeTriggerListSheet->getDefaultRowDimension()->setRowHeight(20);

        $rows = 2;
        $this->activeTriggerListSheet->setCellValue('B' . $rows, '触发器');
        $this->activeTriggerListSheet->mergeCells('B' . $rows . ':F' . $rows);
        $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ]);

        $rows++;
        $this->activeTriggerListSheet->setCellValue('B' . $rows, '序号');
        $this->activeTriggerListSheet->setCellValue('C' . $rows, '表名');
        $this->activeTriggerListSheet->setCellValue('D' . $rows, '触发器名称');
        $this->activeTriggerListSheet->setCellValue('E' . $rows, '触发时机');
        $this->activeTriggerListSheet->setCellValue('F' . $rows, '功能说明');
        $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray($this->tableHeaderStyle);
        $rows++;

        while ($rows <= 23) {
            $this->activeTriggerListSheet->setCellValue('B' . $rows, $rows - 3);
            $this->activeTriggerListSheet->setCellValue('C' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('D' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('E' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('F' . $rows, '');
            $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray($this->tableStyle);
            $rows++;
        }

        return $this;
    }

    public function procedureListWorksheet()
    {
        $this->activeTriggerListSheet = $this->spreadSheet->createSheet();
        $this->activeTriggerListSheet->setTitle($this->config['excel']['worksheet']['procedure']['list']['title']);
        $this->activeTriggerListSheet->getColumnDimension('B')->setWidth(15);
        $this->activeTriggerListSheet->getColumnDimension('C')->setWidth(15);
        $this->activeTriggerListSheet->getColumnDimension('D')->setWidth(50);
        $this->activeTriggerListSheet->getColumnDimension('E')->setWidth(30);
        $this->activeTriggerListSheet->getColumnDimension('F')->setWidth(30);
        $this->activeTriggerListSheet->getDefaultRowDimension()->setRowHeight(20);

        $rows = 2;
        $this->activeTriggerListSheet->setCellValue('B' . $rows, '存储过程');
        $this->activeTriggerListSheet->mergeCells('B' . $rows . ':F' . $rows);
        $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ]
        ]);

        $rows++;
        $this->activeTriggerListSheet->setCellValue('B' . $rows, '序号');
        $this->activeTriggerListSheet->setCellValue('C' . $rows, '名称');
        $this->activeTriggerListSheet->setCellValue('D' . $rows, '参数');
        $this->activeTriggerListSheet->setCellValue('E' . $rows, '返回值');
        $this->activeTriggerListSheet->setCellValue('F' . $rows, '功能说明');
        $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray($this->tableHeaderStyle);
        $rows++;

        while ($rows <= 23) {
            $this->activeTriggerListSheet->setCellValue('B' . $rows, $rows - 3);
            $this->activeTriggerListSheet->setCellValue('C' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('D' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('E' . $rows, '');
            $this->activeTriggerListSheet->setCellValue('F' . $rows, '');
            $this->activeTriggerListSheet->getStyle('B' . $rows . ':F' . $rows)->applyFromArray($this->tableStyle);
            $rows++;
        }

        return $this;
    }

    private function generate()
    {
        $writer = new Xlsx($this->spreadSheet);
        preg_match("/dbname=(.*?);/", $this->config['database']['dsn'], $dbname);
        $fileName = date('ymdhis', time()) . '_' . $dbname[1] . '_MySQLDDIC.xlsx';
        $writer->save($fileName);
        echo '数据字典文件生成成功: ' . dirname(__DIR__) . DIRECTORY_SEPARATOR . $fileName . PHP_EOL;
    }

    public function __destruct()
    {
        unset($this->pdo, $this->spreadSheet, $this->activeTableListSheet);
        exit();
    }
}