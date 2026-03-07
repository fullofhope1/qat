<?php
// includes/classes/CommunicationService.php

class CommunicationService extends BaseService
{
    protected $commRepo;

    public function __construct(CommunicationRepository $commRepo)
    {
        $this->commRepo = $commRepo;
    }

    public function getWhatsAppStatementsData()
    {
        $customers = $this->commRepo->getDebtorsWithActivity();
        $todayDate = date('Y-m-d');

        foreach ($customers as &$c) {
            $lastTrans = array_reverse($this->commRepo->getLastTransactions($c['id']));

            $transLine = "";
            foreach ($lastTrans as $tr) {
                $date = date('m-d', strtotime($tr['t_date']));
                $amt = number_format(abs($tr['amount']));
                $sign = ($tr['amount'] > 0 ? '+' : '-');
                $transLine .= "📅 {$date}: {$tr['t_type']} ({$sign}{$amt})\n";
            }

            $msg = "مرحباً *{NAME}*، 👋\n\nنود إحاطتكم بتفاصيل مديونيتكم لدى *القادري و ماجد* بتاريخ {$todayDate}:\n\n*آخر الحركات:*\n" . ($transLine ?: "لا يوجد حركات مؤخراً\n") . "\n💰 *دينك الإجمالي الحالي:* {AMOUNT} ريال يمني.\n\nيرجى التكرم بالسداد في أقرب وقت لضمان استمرارية التعامل.\n\nشكراً لتعاملكم معنا.\n*القادري و ماجد*";

            $msg = str_replace('{NAME}', $c['name'], $msg);
            $msg = str_replace('{AMOUNT}', number_format($c['total_debt']), $msg);

            $c['encoded_msg'] = rawurlencode($msg);
            $c['formatted_phone'] = $this->formatPhone($c['phone']);
        }

        return $customers;
    }

    protected function formatPhone($phone)
    {
        if (strlen($phone) >= 9) {
            $last9 = substr($phone, -9);
            if (substr($last9, 0, 1) == '7') {
                return '967' . $last9;
            }
        }
        return $phone;
    }

    public function getUnknownTransfersData($limit = 100)
    {
        return $this->commRepo->getUnknownTransfers($limit);
    }

    public function processUnknownTransfer($action, $data)
    {
        if ($action === 'add') {
            return $this->commRepo->createUnknownTransfer($data);
        } elseif ($action === 'update') {
            $id = $data['id'];
            unset($data['id']);
            return $this->commRepo->updateUnknownTransfer($id, $data);
        }
        return false;
    }

    public function linkTransferToCustomer($transferId, $customerId)
    {
        return $this->commRepo->resolveTransfer($transferId, $customerId);
    }
}
