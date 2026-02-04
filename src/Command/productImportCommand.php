<?php


namespace App\Command;

use Elements\Bundle\ProcessManagerBundle\Executor\Action;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Services\productImportService;
use App\Factory\mapping;
use Exception;

class productImportCommand extends AbstractCommand
{
    private $header = [];

    use \Elements\Bundle\ProcessManagerBundle\ExecutionTrait;

    public function __construct(private productImportService $productImportService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('philips:product-import')
            ->setDescription('Import products from Excel file')
            ->addOption(
                'monitoring-item-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Contains the monitoring item if executed via the Pimcore backend'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->initProcessManager($input->getOption('monitoring-item-id'), ['autoCreate' => true]);

            $monitoringItem = $this->getMonitoringItem();
            $callbackSettings = $monitoringItem->getCallbackSettings();
            $logger = $monitoringItem->getLogger();

            $sourceFile = (isset($callbackSettings['productSheetAttachment'])) ? $callbackSettings['productSheetAttachment'] : null;

            $asset = \Pimcore\Model\Asset::getById($sourceFile['id']);
            if (empty($asset)) {
                throw new \Exception("Product master data source file not found!!!");
            }

            $path = $asset->getPath() . $asset->getFilename();
            $systemFilePath = PIMCORE_WEB_ROOT . '/var/assets' . $path;
            if (!file_exists($systemFilePath)) {
                throw new \Exception("ATTENTION!!!!! \nFile does not exist at direcotry {$systemFilePath}\n");
            }

            $reader = new \OpenSpout\Reader\XLSX\Reader();
            $reader->open($systemFilePath);
            $totalSteps = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $excelRow) {
                    if ($totalSteps % 50 == 0) {
                        \Pimcore::collectGarbage();
                    }
                    $totalSteps++;
                }
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                $index = 0;
                $this->header = [];
                $sheetName = strtoupper(trim($sheet->getName()));

                if (array_key_exists($sheetName, mapping::SHEET_MAPPING)) {
                    foreach ($sheet->getRowIterator() as $excelRow) {
                        $rowData = $excelRow->toArray();

                        // skip completely empty rows
                        if (empty(array_filter($rowData))) {
                            continue;
                        }

                        // first non-empty row = header
                        if (empty($this->header)) {
                            $this->header = $rowData;
                            continue;
                        }

                        $this->productImportService->productImportService($rowData, $this->header, mapping::SHEET_MAPPING[$sheetName], $monitoringItem);

                        $index++;
                    }
                } else {
                    $logger->critical("Either one or more sheet name is different from Master_Products, Product_Masters or varaints");
                }

                if ($index % 50 == 0) {
                    \Pimcore::collectGarbage();
                }

                $monitoringItem->setCurrentWorkload($index)->setTotalWorkload($totalSteps)->save();
            }

            $reader->close();

            $logFilePath = PIMCORE_PRIVATE_VAR . '/log/process-manager/' . $monitoringItem->getId() . '.log';
            $monitoringItem->setActions([
                $this->downloadAction($logFilePath),
                $this->openItemAction($logFilePath),
            ]);

            $monitoringItem->save();

            $monitoringItem->setMessage('Job finished')->setCompleted();

            return self::SUCCESS;
        } catch (Exception $e) {
            $monitoringItem->setMessage($e->getMessage());
            throw new Exception($e);
        }
    }

    public function downloadAction($logFilePath)
    {
        $downloadAction = new Action\Download();
        $downloadAction
            ->setAccessKey('myIcon')
            ->setLabel('Download Icon')
            ->setFilePath($logFilePath)
            ->setDeleteWithMonitoringItem(false);

        return $downloadAction;
    }

    public function openItemAction($logFilePath)
    {
        $openItemAction = new Action\OpenItem();
        $openItemAction
            ->setLabel('Log document')
            ->setItemId(1)
            ->setType($logFilePath);

        return $openItemAction;
    }
}
