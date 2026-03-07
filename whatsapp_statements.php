<?php
// whatsapp_statements.php
require_once 'config/db.php';
include_once 'includes/header.php';

// Initialization via Clean Architecture
$commRepo = new CommunicationRepository($pdo);
$service = new CommunicationService($commRepo);

$customers = $service->getWhatsAppStatementsData();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fab fa-whatsapp text-success me-2"></i> كشوفات حساب واتساب</h3>
        <span class="badge bg-secondary rounded-pill"><?= count($customers) ?> عميل مدين</span>
    </div>

    <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fs-4 me-3"></i>
            <div>
                يمكنك الضغط على زر "إرسال" بجانب كل عميل لفتح محادثة واتساب وإرسال كشف الحساب مباشرة.
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 20px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">العميل</th>
                            <th>الجوال</th>
                            <th>الرصيد المتبقي</th>
                            <th class="text-end pe-4">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($c['phone']) ?></td>
                                <td class="fw-bold text-danger"><?= number_format($c['total_debt']) ?> ريال</td>
                                <td class="text-end pe-4">
                                    <a href="https://wa.me/<?= $c['formatted_phone'] ?>?text=<?= $c['encoded_msg'] ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3 py-2 shadow-sm" onclick="markRowSent(this.closest('tr'))">
                                        <i class="fab fa-whatsapp me-1"></i> إرسال كشف الحساب
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fs-1 d-block mb-3 opacity-25"></i>
                                    لا يوجد عملاء عليهم ديون حالياً.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr {
        transition: background-color 0.2s;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(25, 135, 84, 0.03);
    }

    .sent {
        opacity: 0.6;
        background-color: #f8f9fa !important;
    }
</style>

<script>
    function markRowSent(row) {
        row.classList.add('sent');
    }
</script>

<?php include_once 'includes/footer.php'; ?>