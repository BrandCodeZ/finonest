// FINONEST REPLICA - shared interactions
document.addEventListener('DOMContentLoaded', () => {
  // Mobile menu toggle
  const toggle = document.querySelector('.menu-toggle');
  const mobileMenu = document.querySelector('.mobile-menu');
  if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
    });
    mobileMenu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => mobileMenu.classList.remove('open'));
    });
  }

  // Active nav link (based on current page filename)
  const current = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a, .mobile-menu a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === current) a.classList.add('active');
  });

  // Hide EMI calculator FAB on the EMI calculator page itself
  const fabEmi = document.querySelector('#fabEmi');
  if (fabEmi && current === 'emi-calculator.html') fabEmi.style.display = 'none';

  // Year in footer
  const year = document.querySelector('#year');
  if (year) year.textContent = new Date().getFullYear();

  // FAQ accordion
  document.querySelectorAll('.faq-q').forEach(q => {
    q.addEventListener('click', () => {
      const item = q.parentElement;
      item.classList.toggle('open');
    });
  });

  // Animated counters
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const animate = (el) => {
      const target = +el.dataset.count;
      const dur = 1600;
      const start = performance.now();
      const step = (now) => {
        let p = Math.min((now - start) / dur, 1);
        p = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(p * target).toLocaleString('en-IN');
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString('en-IN');
      };
      requestAnimationFrame(step);
    };
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { animate(e.target); io.unobserve(e.target); }
      });
    }, { threshold: 0.4 });
    counters.forEach(c => io.observe(c));
  }

  // Tab switching (e.g. credit card categories)
  const tabs = document.querySelector('#card-tabs');
  if (tabs) {
    tabs.querySelectorAll('.subtab').forEach(btn => {
      btn.addEventListener('click', () => {
        tabs.querySelectorAll('.subtab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        const panel = document.querySelector('.tab-panel[data-panel="' + btn.dataset.tab + '"]');
        if (panel) panel.classList.add('active');
      });
    });
  }

  // Product selector on apply page
  const prodSelect = document.querySelector('#prodSelect');
  const loanTypeInput = document.querySelector('#loanTypeInput');
  let selectedLoan = '';
  if (prodSelect) {
    prodSelect.querySelectorAll('.prod-option').forEach(opt => {
      opt.addEventListener('click', () => {
        prodSelect.querySelectorAll('.prod-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        selectedLoan = opt.dataset.loan;
        if (loanTypeInput) loanTypeInput.value = selectedLoan;
      });
    });
  }

  // Apply/Contact form submit
  document.querySelectorAll('form.loan-form').forEach(loanForm => {
    loanForm.addEventListener('submit', (e) => {
      const realBackend = loanForm.getAttribute('action') === 'submit-application.php';

      // Contact form + static demo forms: show inline success (no backend).
      if (loanForm.id === 'contactForm' || !realBackend) {
        e.preventDefault();
        let success = loanForm.querySelector('.form-success');
        if (!success) {
          success = document.createElement('div');
          success.className = 'form-success';
          loanForm.appendChild(success);
        }
        success.style.display = 'block';
        if (loanForm.id === 'contactForm') {
          success.textContent = '🙏 Thank you! Your message has been received. We will get back to you within 24 hours.';
        } else {
          success.textContent = '🎉 Thank you! Our loan expert will contact you within 24 hours' + (selectedLoan ? ' regarding your ' + selectedLoan + '.' : '.');
          loanForm.reset();
          prodSelect && prodSelect.querySelectorAll('.prod-option').forEach(o => o.classList.remove('active'));
        }
        success.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      // Real loan application: set the hidden loan_type, then let it submit to the server.
      if (loanTypeInput && selectedLoan) loanTypeInput.value = selectedLoan;
      if (prodSelect && !selectedLoan) {
        e.preventDefault();
        let err = loanForm.querySelector('.form-alert-error');
        if (!err) {
          err = document.createElement('div');
          err.className = 'form-alert form-alert-error';
          loanForm.querySelector('h3').after(err);
        }
        err.textContent = 'Please select the type of loan you are looking for.';
        window.scrollTo({ top: loanForm.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
      }
    });
  });

  // ==================== EMI CALCULATOR ====================
  const emiCalc = document.querySelector('#emiCalc');
  if (emiCalc) {
    const $ = (id) => emiCalc.querySelector(id);
    const amountNum = $('#calcAmount'), amountRange = $('#amountRange');
    const rateNum = $('#calcRate'), rateRange = $('#rateRange');
    const tenureNum = $('#calcTenure'), tenureRange = $('#tenureRange');
    const feeNum = $('#calcFee'), feeRange = $('#feeRange');

    const fmtIN = (n) => '₹' + Math.round(n).toLocaleString('en-IN');
    const compactIN = (n) => {
      if (n >= 1e7) return (n / 1e7).toFixed(1).replace(/\.0$/, '') + ' Cr';
      if (n >= 1e5) return (n / 1e5).toFixed(1).replace(/\.0$/, '') + ' L';
      return fmtIN(n);
    };

    const presets = {
      home: { amount: 1200000, rate: 7.0, tenure: 5 },
      car: { amount: 1000000, rate: 7.99, tenure: 7 },
      personal: { amount: 500000, rate: 10.49, tenure: 5 }
    };

    let lastPreset = 'home';

    const calcEMI = (P, annual, years) => {
      const r = annual / 12 / 100;
      const n = Math.round(years * 12);
      if (!n) return 0;
      if (r === 0) return P / n;
      const f = Math.pow(1 + r, n);
      return P * r * f / (f - 1);
    };

    const C = 2 * Math.PI * 15.9; // donut circle circumference

    const update = () => {
      const P = Math.max(0, parseFloat(amountNum.value) || 0);
      const annual = Math.max(0, parseFloat(rateNum.value) || 0);
      const years = Math.max(1, parseFloat(tenureNum.value) || 1);
      const feePct = Math.max(0, parseFloat(feeNum.value) || 0);

      const emi = calcEMI(P, annual, years);
      const totalPay = emi * Math.round(years * 12);
      const totalInt = totalPay - P;
      const feeAmt = P * feePct / 100;

      $('#amountOut').textContent = fmtIN(P);
      $('#rateOut').textContent = annual.toFixed(2) + '%';
      $('#tenureOut').textContent = years + ' yr' + (years > 1 ? 's' : '');
      $('#feeOut').textContent = feePct.toFixed(2) + '%';

      $('.emi-big .v').innerHTML = fmtIN(emi) + ' <span>/ month</span>';
      $('#stTotal').textContent = fmtIN(totalPay);
      $('#stInterest').textContent = fmtIN(totalInt);
      $('#stPrinc').textContent = fmtIN(P);
      $('#stFee').textContent = fmtIN(feeAmt);

      // donut
      const pFrac = totalPay ? Math.max(P / totalPay, 0.0001) : 0;
      const iFrac = Math.max(1 - pFrac, 0);
      $('#donutPrinc').style.strokeDasharray = (pFrac * C) + ' ' + C;
      $('#donutInt').style.strokeDasharray = (iFrac * C) + ' ' + C;
      $('#donutInt').style.strokeDashoffset = -(pFrac * C);
      $('#donutTotal').textContent = compactIN(totalPay);
      $('#lgPrinc').textContent = compactIN(P);
      $('#lgInt').textContent = compactIN(Math.max(totalInt, 0));
    };

    // sync number input <-> range
    const sync = (num, range, tweak) => {
      num.addEventListener('input', () => { range.value = num.value; update(); });
      range.addEventListener('input', () => { num.value = range.value; update(); });
      if (tweak) tweak();
    };
    sync(amountNum, amountRange);
    sync(rateNum, rateRange);
    sync(tenureNum, tenureRange);
    sync(feeNum, feeRange);

    // presets
    emiCalc.querySelectorAll('.loan-presets button').forEach(btn => {
      btn.addEventListener('click', () => {
        emiCalc.querySelectorAll('.loan-presets button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const p = presets[btn.dataset.preset];
        if (!p) return;
        lastPreset = btn.dataset.preset;
        amountNum.value = p.amount; amountRange.value = p.amount;
        rateNum.value = p.rate; rateRange.value = p.rate;
        tenureNum.value = p.tenure; tenureRange.value = p.tenure;
        update();
      });
    });

    update();
  }

  // Loan widget slider (visual)
  document.querySelectorAll('.slider-range').forEach(range => {
    const update = () => {
      const fill = range.parentElement.querySelector('.fill');
      const knob = range.parentElement.querySelector('.knob');
      const valLabel = range.parentElement.querySelector('.val-display');
      const pct = (range.value - range.min) / (range.max - range.min) * 100;
      if (fill) fill.style.width = pct + '%';
      if (knob) knob.style.left = pct + '%';
      if (valLabel) valLabel.textContent = range.value.toLocaleString('en-IN');
    };
    range.addEventListener('input', update);
    update();
  });
});
