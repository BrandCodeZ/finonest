// FINONEST REPLICA - static site builder
// Combines partials/head.html + pages/*.content.html + partials/footer.html
// Run: node build.js
const fs = require('fs');
const path = require('path');

const root = __dirname;
const partialsDir = path.join(root, 'partials');
const pagesDir = path.join(root, 'pages');

const headTemplate = fs.readFileSync(path.join(partialsDir, 'head.html'), 'utf8');
const footerTemplate = fs.readFileSync(path.join(partialsDir, 'footer.html'), 'utf8');

const pages = [
  { file: 'index.html', title: "Finonest | India's Fastest Growing Loan Provider", desc: 'Get home loans, car loans, personal loans & business loans with quick approval and competitive interest rates.' },
  { file: 'home-loan.html', title: 'Home Loan Interest Rates Starting @ 7.3% - Finonest', desc: 'Turn your dream home into reality with home loans starting at 7.30% p.a., doorstep assistance and pre-approved offers.' },
  { file: 'car-loan.html', title: 'Car Loan - Finonest | Rates from 7.99% | 100% Financing Available', desc: 'Drive your dream car today. Car loans with rates from 7.99% p.a., 100% on-road financing and same-day approval.' },
  { file: 'personal-loan.html', title: 'Personal Loan in India - Instant Approval up to ₹40 Lakhs', desc: 'Instant personal loans up to ₹40 lakhs with minimal documentation and 24-hour disbursal.' },
  { file: 'business-loan.html', title: 'Business Loan - Finonest | Working Capital & Term Loans', desc: 'Business loans for working capital and expansion with competitive rates and fast processing.' },
  { file: 'loan-against-property.html', title: 'Loan Against Property - Finonest | Secured Loans up to ₹10 Crore', desc: 'Unlock the value of your property with Loan Against Property. High-value loans with flexible tenure.' },
  { file: 'credit-cards.html', title: 'Credit Cards - Finonest | Best Rewards, Travel & Cashback Cards', desc: 'Find the perfect credit card that matches your lifestyle with No Cost EMI, lounge access and instant approval.' },
  { file: 'emi-calculator.html', title: 'EMI Calculator - Finonest | Home, Car & Personal Loan EMI', desc: 'Calculate your monthly EMI for home, car and personal loans instantly. Compare tenures, interest rates and total payments.' },
  { file: 'about.html', title: 'About Finonest - Best Loan Provider in India | Top DSA Since 2017', desc: 'One of the top auto loan DSAs in India. 18+ branches, 50K+ happy customers and growing since 2017.' },
  { file: 'apply.html', title: 'Apply for Loan - Finonest | Quick & Easy Application', desc: 'Fill in your details — our expert will contact you within 24 hours with the best loan offers.' },
  { file: 'dsa-partner.html', title: 'Become DSA Partner in India - Earn High Commission | Finonest', desc: 'Start your loan business with Finonest. Partner with a trusted loan distribution network.' },
  { file: 'contact.html', title: 'Contact Finonest - Best DSA in Jaipur | Loan Support', desc: 'Get in touch with our loan experts. We are here to help you find the best loan solutions.' },
];

let built = 0;
for (const p of pages) {
  const content = fs.readFileSync(path.join(pagesDir, p.file), 'utf8');
  const page = headTemplate
    .replace('{{TITLE}}', p.title)
    .replace('{{DESC}}', p.desc)
    .replace('{{ACTIVE}}', p.file)
    .replace('<!-- @CONTENT -->', content)
    .replace('<!-- @FOOTER -->', footerTemplate);
  fs.writeFileSync(path.join(root, p.file), page);
  built++;
  console.log('Built ' + p.file);
}
console.log('Done. Built ' + built + ' pages.');