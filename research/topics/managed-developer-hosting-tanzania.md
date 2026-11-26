# Ripoti ya topic: Managed project hosting for developers in Tanzania

- Tarehe ya kuanzisha / kusasisha: 2026-11-26
- Toleo: 2
- Eneo / nchi na watumiaji: Tanzania; independent developers and small software agencies
- Hatua ya sasa: Makes sense
- Hali: Inahitaji uthibitisho

## Muhtasari wa uamuzi

**Uamuzi wa sasa: Jaribu kwanza.** TCC's core deployment and project-database workflows are reported complete by the owner. Proposed offer: Free with five repositories and one database; Plus with unlimited counts at TZS 3,000/month or TZS 10,000/year. Twelve monthly payments total TZS 36,000, so the annual offer is 72.2% cheaper. Tanzania already has low-cost cPanel and reseller hosting offers, so competing on raw hosting price is unlikely to work. A stronger hypothesis is managed project onboarding with clearly bounded resources. Customer demand and willingness to pay are not yet verified.

## 1. Topic — Kufafanua mada

**Hali:** Imekamilika

### Tatizo na mazingira

The owner has built a portal that separates developer project deployment and database requests, with administrator approval and project-specific access. The current pricing hypothesis is a Freemium plan with up to five repository registrations and one database, plus a Plus plan with unlimited repo/database counts at an introductory TZS 10,000 price versus TZS 21,000 standard. Billing period, included resources, and customer willingness to pay still need confirmation.

### Swali kuu na maswali madogo

Will Tanzania-based developers pay for a managed service that connects GitHub projects to hosting and project-specific databases, with setup and support handled through TCC?

- Which developers have this problem often enough to pay?
- Which service matters most: deployment setup, database setup, backups, or support?
- What monthly or annual price covers hosting costs and support time?

### Scope na dhana za mwanzo

Focus first on web developers and small agencies in Tanzania. Assume they already have GitHub repositories and need reliable deployment or database setup. This assumption needs interviews; no demand or willingness-to-pay evidence is available yet.

**Output:** Research brief.
**Msingi na kiwango cha uhakika:** Existing TCC capability and user intent are known from project context; customer demand is unverified.
**Hatua inayofuata:** Interview a small group of potential users before building more features.

## 2. Utafiti — Ushahidi na suluhisho zilizopo

**Hali:** Imekamilika kwa desktop research; user validation pending

### Matokeo muhimu

| Suluhisho lililopo | Walengwa / eneo | Thamani | Gharama na tarehe | Mapungufu | Chanzo |
|---|---|---|---|---|---|
| Mamdali shared hosting | Tanzania | cPanel, SSL, backups, support | TZS 40,000/year Starter; TZS 50,000/year Basic; page accessed 2026-11-26 | Commodity hosting; no developer-project onboarding workflow shown on pricing page | [Mamdali hosting](https://mamdali.co.tz/web-hosting/) |
| NetPoa shared cPanel | Tanzania | Hosting, SSL, weekly backups, local support and local payment options | From TZS 99,000/year according to its FAQ; accessed 2026-11-26 | Not evidence that developers need a separate managed deployment portal | [NetPoa FAQ](https://netpoa.tz/faq) |
| Mianet cPanel hosting | Tanzania | Hosting, domain, email, SSL, daily backups | From TZS 180,000/year; accessed 2026-11-26 | Hosting bundle may not cover per-project database management and GitHub review workflow | [Mianet hosting](https://mianet.co.tz/web-hosting.php) |

### Ushahidi unaopingana na mapengo

**Dai la chanzo:** Local vendors advertise inexpensive hosting bundles, which shows price competition exists; these pages do not prove actual sales, service quality, or customer demand for TCC.

**Haijathibitishwa:** How many developers face repeated deployment/database setup pain, whether they prefer this to a VPS or their existing provider, and what support level they would pay for. No customer interviews or paid trials are recorded.

**Dhana ya bei ya mwenye mradi:** Free includes up to five repos and one database; Plus offers unlimited repo/database count with a proposed TZS 10,000 introductory price against TZS 21,000 standard. It is not yet clear whether these prices are monthly or annual. "Unlimited" counts should not mean unlimited storage, CPU, bandwidth, backup retention, database size, or support time.

**Output:** Findings and alternatives comparison.
**Msingi na kiwango cha uhakika:** Public vendor pricing pages, accessed 2026-11-26; these are vendor claims, not independent market measurements.
**Hatua inayofuata:** Validate frequency, current workaround, payer, and price with prospective users.

## 3. Makes sense — Uhalisia wa fursa

**Hali:** Inaendelea

| Kigezo | Tathmini | Ushahidi / dhana | Pengo / jaribio linalohitajika |
|---|---|---|---|
| Ukubwa na marudio ya tatizo | Unknown | Owner identified the workflow while onboarding developers | Ask potential users for recent examples and frequency |
| Mtumiaji na anayelipa | Likely independent developers or small agencies | Product is designed for developers; payer not validated | Identify who owns hosting decisions and budget |
| Utayari wa kulipa | Unknown | Owner proposes TZS 3,000/month or TZS 10,000/year; yearly is 72.2% below twelve monthly payments | Present both billing options and ask for a paid commitment |
| Thamani ya web technology | Plausible | Portal can standardize approvals, paths, project databases, and status | Compare with cPanel, GitHub Actions, and unmanaged VPS workflows |
| Uwezekano wa kiteknolojia na utegemezi | Core prototype exists | Depends on shared-host limits, cPanel API, SMTP, backups, and operator response | Measure costs and reliability on current plan |
| Gharama na mapato yanayokadiriwa | Not estimated; annual Plus price may be too low if it includes hosting | Hosting plan cost, per-project capacity, and support hours are unknown; public basic hosting offers start around TZS 40,000/year | Calculate cost per active project/database before adopting TZS 10,000/year |
| Njia ya kupata wateja | Unknown | Owner has a working portal and developer onboarding flow | Test referrals and direct one-to-one demos with consent |
| Ushindani na sababu ya kuhama | High on basic hosting price; differentiated support is a hypothesis | Public Tanzanian providers advertise shared hosting from TZS 40,000–180,000/year | Ask what users would switch for and quantify support value |
| Masharti ya eneo yanayoathiri utekelezaji | Local billing/support may help | Providers advertise local currency and support; actual preference untested | Ask about preferred payment and support channels |

| Kigezo | Tathmini | Ushahidi / dhana | Pengo / jaribio linalohitajika |
|---|---|---|---|
| Freemium acquisition | Plausible, but five repos plus a database may allow meaningful hosting costs before payment | Owner's current offer hypothesis | Model the worst-case active sites, DB storage, backups, and support usage per free account |
| Plus at TZS 3,000/month vs 10,000/year | Annual option is 72.2% cheaper than twelve monthly payments | Arithmetic on owner's proposed prices | Check whether annual payment covers hosting, backup, payment fees, and support |
| Unlimited counts | Risky as an infrastructure promise without fair-use limits | Existing shared cPanel account and unknown capacity | Define storage, bandwidth, active deployments, database sizes, backups, and concurrency ceilings |

**Uamuzi:** Jaribu kwanza.
**Sababu na kinachoweza kubadilisha uamuzi:** Don't add more technical features until potential users confirm the problem and price. TZS 10,000/year is below several published basic-hosting plans; it may not cover TCC's hosting and support costs. Continue only if at least two users commit to a pilot price above measured cost. Avoid promising unlimited hosting/database capacity; publish fair-use limits. The owner should also decide whether five repositories means registered repos or five live hosted projects.
**Output:** Opportunity assessment.
**Msingi na kiwango cha uhakika:** Moderate confidence in the recommendation to validate first; low confidence in demand and pricing.
**Hatua inayofuata:** Run a small customer-discovery and paid-pilot experiment.

## 4. Innovation — Suluhisho linalopendekezwa

**Hali:** Rasimu

| Wazo | Pengo linalotatuliwa | Thamani kwa mtumiaji | Tofauti na alternatives | Uwezekano / utegemezi | Dhana muhimu |
|---|---|---|---|---|---|
| Managed project launch: repository review, approved deployment, optional isolated project DB, basic backup guidance, and Swahili/English support | Developers spend time configuring hosting, permissions, deployment, and databases | One clear request path and no main cPanel access | Service and controlled workflow around existing hosting; uniqueness is not established | Uses current TCC; depends on safe isolation, hosting capacity, and support processes | Developers value the saved time enough to pay |

### Concept inayochaguliwa

Sell an outcome, not a portal: “We set up and manage deployment and a project database for your web project, while you keep control of your GitHub repository and never receive shared cPanel credentials.” Draft Free: five repositories and one database. Draft Plus: unlimited counts at TZS 3,000/month or TZS 10,000/year. Treat these as hypotheses until costs and paid demand are validated. Clarify whether one Free database serves one project only; sharing it across multiple projects conflicts with the project's database-isolation design.

### Hatari na namna ya kupima

Risks include commodity price pressure, one shared cPanel account becoming a security or capacity bottleneck, manual support consuming margin, and unclear responsibility for backups or incidents. Test with a small number of projects and publish explicit limits before expansion.

**Output:** Innovation concept; differentiation remains a hypothesis.
**Msingi na kiwango cha uhakika:** Based on existing product design and competitor offer pages; no proof of uniqueness or demand.
**Hatua inayofuata:** Prepare a one-page offer and cost sheet; don't buy additional hosting capacity yet.

## 5. Possible output — Kitu kinachoweza kutengenezwa

**Hali:** Rasimu

- Aina ya output na sababu ya kuichagua: A manual paid-pilot offer; tests business demand without more platform development.
- Mtumiaji wa kwanza na safari yake muhimu: A Tanzania-based developer with a GitHub web project; discuss need, show the working flow, agree scope and price, onboard one approved project.
- Vipengele vya awali na mipaka ya MVP: GitHub deployment request, admin-approved hosting path, optional project DB request, stated backup/support limits. Keep manual approvals; do not promise 24/7 support or unlimited capacity.
- Data, vifaa, integrations, na washirika wanaohitajika: Existing TCC, cPanel resources, email delivery, GitHub App/workflow, and a written support/backup process.
- Dhana ya mapato na gharama: Owner-proposed Free: five repositories + one database. Owner-proposed Plus: unlimited counts for TZS 3,000/month or TZS 10,000/year. Annual price is 72.2% below twelve monthly payments. Verify what “unlimited” includes and calculate hosting, domain, backup/storage, payment, and support costs before launch.
- Makadirio ya muda na msingi wake: Not estimated; confirm time with the first pilot.

| Dhana ya kupima | Jaribio | Washiriki / data | Kipimo | Kigezo cha kufaulu / kutofaulu | Uamuzi baada ya matokeo |
|---|---|---|---|---|---|
| Developers need managed deployment/database setup | 5 structured conversations about recent projects and current workaround | 5 target developers/agencies | Number with a recent concrete need; workaround time/cost | Pass if at least 3 describe a recent recurring problem; otherwise narrow or revisit segment | Refine target and offer |
| Target users will pay for the service | Present Free and Plus, disclose exact resource limits and both billing terms, ask for paid commitment | Same interviewees | Paid commitments and chosen billing term | Pass if at least 2 commit above measured variable cost; fail if interest stops at “free/cheaper” | Run pilot or revise/pause offer |
| Free tier stays within affordable cost | Model and observe the highest expected Free use with five repos and one DB | Actual hosting limits and pilot usage | Cost per account, active sites, storage, support minutes | Set caps so a Free user cannot consume the hosting plan or support margin; revise quota if the cap is reached too quickly | Keep, reduce, or gate the Free allowance |

**Output:** Pilot experiment proposal; it has not been run.
**Msingi na kiwango cha uhakika:** Proposed thresholds are decision rules, not market facts.
**Hatua inayofuata:** Build the cost sheet and offer; then ask the owner to authorize outreach/pilots separately.

## Source ledger

| ID | Kichwa / mchapishaji | URL | Tarehe ya kuchapishwa / kusasishwa | Tarehe ya kufikiwa | Dai linaloungwa mkono | Mipaka |
|---|---|---|---|---|---|---|
| S1 | Web Hosting, Mamdali | https://mamdali.co.tz/web-hosting/ | Not listed | 2026-11-26 | Published TZS 40,000/year Starter and TZS 50,000/year Basic plans | Vendor's advertised prices; terms may change |
| S2 | Frequently Asked Questions, NetPoa | https://netpoa.tz/faq | Not listed | 2026-11-26 | FAQ states shared cPanel hosting starts at TZS 99,000/year | Provider statement; not independent price survey |
| S3 | Web Hosting, Mianet | https://mianet.co.tz/web-hosting.php | Not listed | 2026-11-26 | Page states cPanel hosting starts at TZS 180,000/year | Provider's advertised starting price; exact features differ by plan |

## Historia ya maamuzi

| Tarehe | Hatua | Uamuzi / kilichobadilika | Ushahidi au sababu |
|---|---|---|---|
| 2026-11-26 | Topic → Possible output | Recommend validating owner-proposed Free/Plus plans before more technical feature work | TCC core workflows are reported complete; advertised hosting prices show commodity competition; demand and unit economics are unvalidated |

## Maswali yaliyo wazi

- Which developer segment has the strongest repeated need: solo developers, agencies, or students building client projects?
- What do cPanel, domain, backup, and support costs add up to per active project?
- Are TZS 10,000 and TZS 21,000 monthly prices, and how long does the introductory offer last?
- How many of the five Free repositories can be actively hosted, and what storage/backup limits apply?
- Would users pay for managed onboarding/support, and what support response time can the business actually sustain?
- What capacity, backup, and incident procedures are required before onboarding more than a few pilot projects?
