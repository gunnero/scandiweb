# Design QA — Scandiweb storefront

## Comparison target

- Source visual truth: Figma `Full Stack Test Designs`, [Category node 150:5](https://www.figma.com/file/Keu02BI0W7eQpWn0AvqnVK/Full-Stack-Test-Designs?type=design&node-id=150-5&mode=design), plus decoded sibling frames Cart Overlay `150:747` and PDP `150:1168`.
- Implementation: `http://localhost:3000/category/all` and product routes served by the local Vite application.
- Source frame sizes: Category `1440 × 1513`, Cart Overlay `1440 × 1513`, PDP `1440 × 933` CSS px.
- Source capture pixels: each browser source capture is `1280 × 720` px with the selected Figma frame rendered at approximately 49–50% zoom and DPR 1.
- Implementation captures: Category `1265 × 1968`, PDP `1265 × 760`, and Cart `1280 × 720` px at DPR 1. The final comparison rendered the application in `1440px`-wide live iframes and scaled both source and implementation to 41.67%.
- Normalization: the Figma frame crop and live implementation use the same `1440px` CSS viewport and the same `0.4167` display scale. Browser canvas chrome and Figma comments/toolbars were excluded from fidelity judgments.
- State: Category source `Women` versus implementation `All`; PDP source `Running Shorts` versus implementation `iPhone 12 Pro`, both with two attribute groups and no selection; cart source has two lines totaling three items, and implementation has Nike ×1 plus Jacket ×2. Dynamic product imagery/copy intentionally comes from the required `data.json` catalog.

## Evidence

- Full-view comparison: `design-evidence/normalized-comparison.png` (`1425 × 1444` px).
- Reopenable comparison viewer: `design-evidence/comparison.html`.
- Source captures: `design-evidence/figma-category-source.png`, `design-evidence/figma-pdp-source.png`, `design-evidence/figma-cart-source.png`.
- Rendered implementation: `design-evidence/implementation-category-after.png`, `design-evidence/implementation-pdp-after.png`, `design-evidence/implementation-cart-after.png`.
- Responsive check: `design-evidence/responsive-check.png`, containing category, PDP, and empty-cart views at `390 × 844` CSS px.
- Focused-region evidence: separate crops were unnecessary after the final full-view comparison because the decoded Figma scenegraph and browser DOM measurements exposed the critical regions at exact CSS dimensions. The VSF logo, PDP geometry, and cart geometry were also checked individually at full resolution.

## Findings

No actionable P0, P1, or P2 differences remain.

- Fonts and typography: Raleway 300/400/500/600/700, Roboto, Roboto Condensed 700, and Source Sans Pro are loaded and used in their source roles. The PDP uses one 30px/600 product heading. The cart casing is `My Bag, 3 items`, matching the source.
- Spacing and layout rhythm: the PLP heading begins at `y=160`, cards at `y=331`, and rows use the source 103px gap. The PDP uses 79×80 thumbnails at 20px gaps, a 575×478 main image area, a 292px information column at source-equivalent `x=903`, and the source CTA/description rhythm. The cart is 325×628 with 16px horizontal padding, 293px rows, exact 167/164px row heights, a 40px row gap, and a 292×43 CTA.
- Colors and visual tokens: the `#5ECE7B` primary, `#1D1F22` ink, `#393748` at 22% cart backdrop, disabled gray, and source shadow opacities match the decoded design tokens.
- Image quality and asset fidelity: product images remain the required catalog assets with `object-fit: contain`. The header now uses the exact 41×41 Figma/Vue Storefront vector asset and decoded gradient paths rather than an approximation.
- Copy and content: fixed interface copy matches the design. Product names, prices, attributes, and imagery differ only because the assignment requires the supplied catalog data.
- Icons and states: cart, quantity, gallery, selected attributes, disabled CTA, out-of-stock, hover Quick Shop, and overlay states are present. Gallery arrows are 32×32 and the overlay omits the non-source close icon while retaining backdrop, header-toggle, and Escape closing behavior.
- Responsiveness/accessibility: the 390×844 board shows a single-column PLP, stacked PDP, scrollable navigation, and full-width cart without horizontal clipping. Semantic controls, visible keyboard focus, alt text, reduced-motion handling, dialog labeling, and keyboard dismissal remain intact.

## Comparison history

### Iteration 1 — blocked

- P1: missing Raleway 300, Source Sans Pro, and Roboto caused fallback typography.
- P1: PDP used a fluid ~797px gallery, 620px height, 86px thumbnails, ~341px information column, and an extra brand line.
- P1: cart was 420px wide with oversized rows/images and a visible close icon absent from the source.
- P2: PLP vertical rhythm began about 40px too early.
- P2: the header logo was an approximate CSS tile/local bag drawing.

Fixes: loaded all source fonts and weights; matched decoded PLP/PDP/cart dimensions; reduced gallery arrows to 32px; changed the PDP to one product heading; removed the extra cart close control; restored source cart casing; matched source shadows and out-of-stock opacity; and installed the exact decoded VSF logo SVG.

### Iteration 2 — passed

- Post-fix evidence: `design-evidence/normalized-comparison.png` renders source and implementation together at the same 1440px viewport and state class.
- Browser measurements match the decoded source: PDP title/labels/options/price/CTA at `y=160/219/245/412/506`, description at `y=598`; cart panel/header/rows/total/CTA at `y=78/110/168/571/631`.
- No remaining visible P0/P1/P2 issue was found. Differences in subjects and strings are expected assignment-data differences, not design drift.

## Functional verification

- Category navigation reached `/category/all` without a reload.
- PDP capacity and color options selected correctly and enabled Add to Cart.
- Add to Cart opened the overlay; quantity changed `1 → 2 → 1`; backdrop close worked.
- Gallery next/previous changed and restored the active image.
- Temporary visual-test items were removed; final preview cart count is zero.
- Browser console errors/warnings after the primary flow: none.
- Frontend: 5 test files and 10 tests passed; TypeScript check passed; Vite production build passed.
- Backend: 11 tests and 49 assertions passed; PHPCS passed.

## Implementation checklist

- [x] Match the Category, PDP, and Cart Overlay Figma frames.
- [x] Use the exact source logo and font families.
- [x] Preserve GraphQL-backed catalog and cart/order behavior.
- [x] Verify primary interactions and clean console output in the in-app browser.
- [x] Verify the desktop comparison and a 390×844 responsive board.

final result: passed
