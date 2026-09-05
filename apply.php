<?php
/**
 * ============================================================
 * Apply for a Loan (dynamic)
 * ============================================================
 * Identical design to the original apply.html, but the loan
 * selector is populated from the Active products in the database.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$applyProducts = $pdo->query(
    'SELECT * FROM products WHERE status = 1 ORDER BY product_name'
)->fetchAll();

$pageTitle = 'Apply for Loan - Finonest | Quick &amp; Easy Application';
$pageDescription = 'Fill in your details — our expert will contact you within 24 hours with the best loan offers.';
$activePage = 'apply.php';
require __DIR__ . '/includes/site_header.php';
?>

<!-- ============ PAGE HERO ============ -->
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> &nbsp;/&nbsp; Apply for Loan</div>
    <h1>Apply for a Loan</h1>
    <p>Fill in your details — our expert will contact you within 24 hours with the best offers.</p>
  </div>
</section>

<!-- ============ FORM + INFO ============ -->
<section class="section">
  <div class="container two-col">
    <div>
      <div class="section-head" style="text-align:left;margin:0 0 30px">
        <span class="section-eyebrow">Easy Application</span>
        <h2 style="font-size:30px">Get Your Loan Fast</h2>
        <p>Step 1 of 2 — tell us the loan you need and your basic details.</p>
      </div>
      <form class="loan-form" id="loanForm">
        <h3>Loan Application Form</h3>
        <div class="form-sub">All fields are required. Your data is 100% secure.</div>
        <div class="form-group full" style="margin-bottom:16px">
          <label>What type of loan are you looking for?</label>
          <div class="prod-select" id="prodSelect">
            <?php if ($applyProducts): ?>
              <?php foreach ($applyProducts as $prod): ?>
                <div class="prod-option" data-loan="<?= e($prod['product_name']) ?>">
                  <?php if (product_image_url($prod['image']) !== default_product_image()): ?>
                    <img class="po-ic" src="<?= e(product_image_url($prod['image'])) ?>" alt="<?= e($prod['product_name']) ?>" style="object-fit:cover" />
                  <?php else: ?>
                    <div class="po-ic">🏦</div>
                  <?php endif; ?>
                  <?= e($prod['product_name']) ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="prod-option"><div class="po-ic">🏦</div>No products available yet</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" placeholder="Enter your full name" required>
          </div>
          <div class="form-group">
            <label>Mobile Number</label>
            <input type="tel" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
          </div>
          <div class="form-group">
            <label>Email (optional)</label>
            <input type="email" placeholder="you@example.com">
          </div>
          <div class="form-group">
            <label>City</label>
            <input type="text" placeholder="Your city" required>
          </div>
          <div class="form-group">
            <label>Employment Type</label>
            <select required>
              <option value="">Select employment</option>
              <option>Salaried</option>
              <option>Self Employed</option>
              <option>Business Owner</option>
              <option>Freelancer</option>
            </select>
          </div>
          <div class="form-group">
            <label>Monthly Income (₹)</label>
            <input type="number" placeholder="e.g. 50000" required>
          </div>
          <div class="form-group full">
            <label>Loan Amount Needed (₹)</label>
            <input type="number" placeholder="e.g. 1000000" required>
          </div>
          <div class="form-group full">
            <label>Message (optional)</label>
            <textarea rows="3" placeholder="Tell us anything about your requirement"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-orange btn-lg">Submit Application →</button>
        <p class="form-note">By submitting, you agree to be contacted by our loan experts. No account required.</p>
      </form>
    </div>

    <div>
      <div class="info-card" style="margin-bottom:24px">
        <h3>How It Works</h3>
        <div class="ic-sub">Here's what happens after you apply</div>
        <div class="ic-rows">
          <div class="ic-row"><span class="l">1. Application submitted</span><span class="r" style="font-weight:600">✓</span></div>
          <div class="ic-row"><span class="l">2. Expert calls in 24 hrs</span><span class="r" style="font-weight:600">✓</span></div>
          <div class="ic-row"><span class="l">3. Best offers compared</span><span class="r" style="font-weight:600">✓</span></div>
          <div class="ic-row"><span class="l">4. Approval &amp; disbursal</span><span class="r" style="font-weight:600">✓</span></div>
        </div>
      </div>
      <div class="contact-grid" style="grid-template-columns:1fr">
        <div class="contact-cell">
          <div class="cc-ic">📞</div>
          <h4>Prefer to Talk?</h4>
          <p>Call us at <strong>+91 94625 53887</strong></p>
          <p style="margin-top:4px">Mon – Sat · 9:30 AM – 7:00 PM</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ WHY APPLY HERE ============ -->
<section class="section section-alt" style="padding-bottom:100px">
  <div class="container">
    <div class="section-head">
      <span class="section-eyebrow">Why Apply With Us</span>
      <h2>One Application, 50+ Banks</h2>
      <p>Single form. Multiple offers. Zero fees. We do the comparison so you get the best deal.</p>
    </div>
    <div class="why-grid">
      <div class="why-item">
        <div class="wi-icon bg-blue">📊</div>
        <h3>Best Offer Comparison</h3>
        <p>We check your profile against 50+ lenders and present the lowest rate options together.</p>
      </div>
      <div class="why-item">
        <div class="wi-icon bg-green">🔒</div>
        <h3>No CIBIL Hit Upfront</h3>
        <p>Your credit score is only pulled after you pick a lender — no impact from browsing offers.</p>
      </div>
      <div class="why-item">
        <div class="wi-icon bg-orange">⚡</div>
        <h3>Zero Service Fees</h3>
        <p>Using Finonest is free. We earn from partner banks, never from you.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>