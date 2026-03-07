<?php
// includes/reports/view_printable.php

// 1. DATA PREP
$breakdowns = $service->getSummaryBreakdowns($reportType, $date, $month, $year);
$ps = $breakdowns['sales'];
$depositsRaw = $breakdowns['deposits'];
$depYER = $depositsRaw['YER'] ?? 0;
$depSAR = $depositsRaw['SAR'] ?? 0;
$depUSD = $depositsRaw['USD'] ?? 0;

$listDebtSales = $reportRepo->getDebtSalesList($reportType, $date, $month, $year);

// CALCULATIONS
$totalSalesWithTransfers = $ps['cash_sales'] + $ps['transfer_sales'];
$totalCashInflow = $ps['cash_sales'] + $collectedPayments;
$netResult = $totalCashInflow - $totalExpenses - $cashRefunds - $depYER;

if ($reportType === 'Monthly') {
    $periodDisplay = "شهر: " . date('Y / m', strtotime($month . "-01"));
} elseif ($reportType === 'Yearly') {
    $periodDisplay = "سنة: " . $year;
} else {
    $periodDisplay = "تاريخ: " . date('Y / m / d', strtotime($date));
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700;800&display=swap');

    :root {
        --print-black: #000000;
        --print-grey: #444444;
        --print-light-grey: #f0f0f0;
    }

    .printable-container {
        font-family: 'Tajawal', sans-serif;
        color: var(--print-black);
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
        direction: rtl;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
            -webkit-print-color-adjust: exact;
        }

        .printable-container {
            padding: 0;
            width: 100%;
            border: none;
        }

        .invoice-card {
            box-shadow: none !important;
            border: none !important;
        }
    }

    .invoice-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 0;
        padding: 40px;
    }

    .invoice-header {
        border-bottom: 3px solid var(--print-black);
        padding-bottom: 25px;
        margin-bottom: 30px;
    }

    .company-logo {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border: 3px solid var(--print-black);
        padding: 2px;
    }

    .invoice-title {
        font-family: 'Amiri', serif;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .meta-value {
        font-weight: 700;
        color: var(--print-black);
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }

    .premium-table th {
        background: var(--print-black);
        color: #fff;
        text-align: right;
        padding: 12px 15px;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .premium-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        font-size: 0.95rem;
    }

    .premium-table tr:last-child td {
        border-bottom: none;
    }

    .summary-section {
        background: var(--print-light-grey);
        padding: 25px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #bbb;
    }

    .summary-row:last-child {
        border-bottom: none;
        padding-top: 15px;
        margin-top: 10px;
        border-top: 2px solid var(--print-black);
    }

    .performance-badge {
        display: inline-block;
        padding: 10px 20px;
        border: 2px solid var(--print-black);
        font-weight: 800;
        font-size: 1.25rem;
        margin-top: 15px;
    }

    .footer-signature {
        margin-top: 60px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 150px;
        margin: 40px auto 10px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-5 no-print">
    <div>
        <h4 class="mb-1 text-dark fw-bold">نظام طباعة التقارير المحترف</h4>
        <p class="text-muted mb-0">نسخة محسنة للعرض الورقي والأرشفة</p>
    </div>
    <button onclick="triggerPrint()" class="btn btn-dark btn-lg rounded-pill px-5 shadow">
        <i class="fas fa-print me-2 text-warning"></i> طباعة التقرير (Ctrl + P)
    </button>
</div>

<div id="printableArea" class="printable-container">
    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-3 text-start">
                    <img src="logo.jpg" alt="Logo" class="company-logo rounded-circle">
                </div>
                <div class="col-6 text-center">
                    <h1 class="invoice-title">مجموعة القادري و ماجد</h1>
                    <p class="fs-5 mb-1">لتجارة وتوريد أجود أنواع القات</p>
                    <p class="text-muted small mb-0">تلفون: 775065459 | 774456261</p>
                </div>
                <div class="col-3 text-end">
                    <div class="p-3 border border-dark rounded">
                        <div class="small text-muted mb-1 text-center border-bottom pb-1">تقرير مالي</div>
                        <div class="fw-bold text-center h5 mb-0"><?= $periodDisplay ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <!-- Left: Summaries -->
            <div class="col-5">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <span class="bg-black text-white p-2 rounded me-2"><i class="fas fa-file-invoice"></i></span>
                    خلاصة النشاط المالي
                </h5>
                <div class="summary-section">
                    <div class="summary-row">
                        <span>إجمالي المبيعات (نقداً + حوالات):</span>
                        <span class="meta-value"><?= number_format($totalSalesWithTransfers) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>إجمالي التحصيل (سداد ديون):</span>
                        <span class="meta-value text-success">+ <?= number_format($collectedPayments) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>إجمالي المصروفات التشغيلية:</span>
                        <span class="meta-value text-danger">- <?= number_format($totalExpenses) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>إجمالي المرتجعات النقدية:</span>
                        <span class="meta-value text-danger">- <?= number_format($cashRefunds) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>إجمالي الإيداعات المسلمة:</span>
                        <span class="meta-value text-primary">- <?= number_format($depYER) ?></span>
                    </div>
                    <div class="summary-row h5">
                        <span class="fw-bold">صافي نتيجة الحركة:</span>
                        <span class="fw-bold <?= $netResult >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($netResult) ?> ريال
                        </span>
                    </div>
                </div>

                <div class="mt-5">
                    <h5 class="fw-bold mb-3">تفاصيل العملات السائلة</h5>
                    <table class="premium-table text-center">
                        <thead>
                            <tr>
                                <th>العملة</th>
                                <th>المبلغ المسلم</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>يمني (YER)</td>
                                <td class="fw-bold"><?= number_format($depYER) ?></td>
                            </tr>
                            <tr>
                                <td>سعودي (SAR)</td>
                                <td class="fw-bold"><?= number_format($depSAR, 2) ?></td>
                            </tr>
                            <tr>
                                <td>دولار (USD)</td>
                                <td class="fw-bold"><?= number_format($depUSD, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Detailed Lists -->
            <div class="col-7">
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-truck-moving me-2 opacity-50"></i>
                        سجل التوريد (المشتريات)
                    </h5>
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>اسم المورد / الرعوي</th>
                                <th class="text-end">المبلغ المستحق</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totP = 0;
                            foreach ($listPurch as $p):
                                $totP += $p['net_cost'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['prov_name']) ?> <small class="text-muted">(<?= $p['type_name'] ?>)</small></td>
                                    <td class="text-end fw-bold"><?= number_format($p['net_cost']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($listPurch)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted">لا توجد عمليات توريد مسجلة</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td>إجمالي الالتزامات للشراء</td>
                                <td class="text-end h6 mb-0"><?= number_format($totP) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div>
                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="fas fa-wallet me-2 opacity-50"></i>
                        سجل المصروفات (النثريات)
                    </h5>
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>البيان / الوصف</th>
                                <th class="text-end">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listExp as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['description']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($e['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($listExp)): ?>
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted">لا توجد مصروفات مسجلة</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td>إجمالي المصروفات</td>
                                <td class="text-end h6 mb-0"><?= number_format($totalExpenses) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Remaining Cash Banner -->
        <div class="mt-5 pt-4 text-center border-top border-dark border-2">
            <div class="h5 text-muted mb-2">رصيد العهدة النقدية المتبقية (عند المحاسب)</div>
            <div class="display-4 fw-black text-dark mb-0"><?= number_format($remainingCash) ?> ريال يمني</div>
            <div class="small opacity-50 mt-1">تاريخ ووقت الاستخراج: <?= date('Y-m-d H:i') ?></div>
        </div>

        <!-- Footer / Signatures -->
        <div class="footer-signature row mt-5">
            <div class="col-4 text-center">
                <p class="fw-bold">المحاسب المسؤول</p>
                <div class="signature-line"></div>
            </div>
            <div class="col-4 text-center">
                <p class="fw-bold">توقيع المستلم (المندوب)</p>
                <div class="signature-line"></div>
            </div>
            <div class="col-4 text-center">
                <p class="fw-bold">اعتماد الإدارة</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
</div>