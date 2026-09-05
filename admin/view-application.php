<?php
/**
 * ============================================================
 * View / Update Loan Application
 * ============================================================
 * Shows complete applicant details and allows the admin to
 * change the application status and add admin notes. Updates
 * POST to update-application.php.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Application Details';

// ---- Validate ID from query string ----
$id = get('id');
if ($id === '' || !ctype_digit($id)) {
    set_flash('danger', 'Invalid application ID.');
    redirect('applications.php');
}
$id = (int) $id;

$stmt = $pdo->prepare('SELECT * FROM loan_applications WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$application = $stmt->fetch();

if (!$application) {
    set_flash('danger', 'Application not found.');
    redirect('applications.php');
}

include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:18px">
  <a href="applications.php" class="btn btn-ghost btn-sm">← Back to Applications</a>
</div>

<div class="app-detail-grid">
  <!-- ===== Applicant details ===== -->
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>Applicant Details</h2>
        <p>Application #<?= (int)$application['id'] ?> · Submitted on <?= e(date('d M Y, h:i A', strtotime($application['created_at']))) ?> · Last updated <?= e(date('d M Y, h:i A', strtotime($application['updated_at']))) ?></p>
      </div>
      <div><?= loan_status_badge($application['application_status']) ?></div>
    </div>

    <div class="detail-list">
      <div class="detail-row">
        <div class="dr-label">Full Name</div>
        <div class="dr-value"><?= e($application['full_name']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Mobile Number</div>
        <div class="dr-value"><?= e($application['mobile_number']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Email</div>
        <div class="dr-value"><?= $application['email'] ? e($application['email']) : '—' ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">City</div>
        <div class="dr-value"><?= e($application['city']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Employment Type</div>
        <div class="dr-value"><?= e($application['employment_type']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Monthly Income</div>
        <div class="dr-value"><?= inr($application['monthly_income']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Loan Amount</div>
        <div class="dr-value"><?= inr($application['loan_amount']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Loan Type</div>
        <div class="dr-value"><?= e($application['loan_type']) ?></div>
      </div>
      <div class="detail-row">
        <div class="dr-label">Message</div>
        <div class="dr-value"><?= $application['message'] ? nl2br(e($application['message'])) : '—' ?></div>
      </div>
    </div>
  </div>

  <!-- ===== Status update ===== -->
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>Update Status &amp; Notes</h2>
        <p>Change the application status and add internal notes.</p>
      </div>
    </div>

    <form method="post" action="update-application.php" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
      <input type="hidden" name="id" value="<?= (int)$application['id'] ?>" />

      <div class="form-group">
        <label for="application_status">Application Status</label>
        <select class="form-control" id="application_status" name="application_status">
          <?php foreach (loan_statuses() as $st => $info): ?>
            <option value="<?= e($st) ?>" <?= $application['application_status'] === $st ? 'selected' : '' ?>>
              <?= e($info['label']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-top:14px">
        <label for="admin_notes">Admin Notes</label>
        <textarea class="form-control" id="admin_notes" name="admin_notes"
                  placeholder="Add a note, e.g. call logs, next steps, remarks..."><?= e($application['admin_notes'] ?? '') ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="applications.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
