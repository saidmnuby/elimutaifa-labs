# Ripoti ya topic: MALIPOPAY (Lockwood) kwa malipo ya TechwareTz Control Center

- Tarehe ya kuanzisha / kusasisha: 2026-11-27
- Toleo: 1.0
- Eneo / nchi na watumiaji: Tanzania; watumiaji wa TCC wanaonunua Plus kwa TZS 20,000 kwa mwezi au mwaka
- Hatua ya sasa: Kuchagua payment provider kabla ya kuanza integration
- Hali: Inahitaji uthibitisho

## Muhtasari wa uamuzi

MALIPOPAY ni mgombea wa kiufundi anayefaa kuendelea kumchunguza: nyaraka zake zina API ya kupokea malipo ya mobile money, benki na USSD, pamoja na njia tofauti ya card. Ada zilizochapishwa ni 2.5% kwa mobile money na benki, na 3% kwa card. Hata hivyo, sijapata uthibitisho huru wa leseni ya Lockwood katika orodha ya BoT niliyoangalia; jina lake halikuonekana kwenye ripoti ya mwaka 2025. Hii haithibitishi kuwa haina leseni, kwa kuwa huenda leseni imesajiliwa kwa jina jingine au taarifa haijasasishwa.

Pendekezo la sasa: **endelea na MALIPOPAY kama chaguo la sandbox/majaribio baada ya kupata majibu yaliyoandikwa kuhusu jina la kisheria na leseni, ada halisi, na masharti ya card. Usibadilishe integration iliyopo au kuweka live kabla ya uthibitisho huo.** Kikwazo kikubwa cha utekelezaji ni kwamba nyaraka mbili za MALIPOPAY zinatoa maelezo yanayokinzana kuhusu webhook retries; tumia status API ya reconciliation kama chanzo cha mwisho cha malipo.

## 1. Topic — Kufafanua mada

**Hali:** Imekamilika

### Tatizo na mazingira

TCC inahitaji kupokea malipo ya Plus kwa njia rahisi kwa wateja wa Tanzania, hasa mobile money/USSD, na pia kadi. Selcom integration imeandaliwa kwenye project; swali ni kama Lockwood/MALIPOPAY ni mbadala unaofaa kabla ya kuendelea na credentials na go-live.

### Swali kuu na scope

Je, MALIPOPAY ina njia za malipo, gharama, uhalali na mchakato wa settlement unaofaa kwa subscription ya TZS 20,000? Tathmini hii inahusu taarifa za wazi mtandaoni pekee; haijajaribu API, haijafungua akaunti ya merchant, na haijathibitisha moja kwa moja na BoT au kampuni.

**Msingi na kiwango cha uhakika:** Juu kwa yaliyomo kwenye docs rasmi za provider; wastani/chini kwa madai ya biashara na uhalali ambayo hayajathibitishwa huru.

## 2. Utafiti — Ushahidi na suluhisho zilizopo

**Hali:** Imekamilika kwa ushahidi wa umma; uthibitisho wa provider bado unahitajika.

### Matokeo muhimu

| Kipengele | Matokeo | Aina ya dai / uhakika | Chanzo na mipaka |
|---|---|---|---|
| Mobile money na USSD | API moja ya collection; docs zinaonyesha push ya simu, na status ya mwisho hupatikana kwa webhook au status lookup. USSD push ina timeout ya sekunde 90. | Dai la docs rasmi; juu | [Collection flow](https://developers.malipopay.co.tz/guides/flows/payment-collection/) |
| Channels | Provider anasema anasaidia M-Pesa, Mixx by Yas, Airtel, HaloPesa, T-Pesa, CRDB/NMB, USSD na Visa/Mastercard/UnionPay/Amex. | Dai la provider; wastani | [FAQ](https://malipopay.co.tz/faq) |
| Ada za kupokea | 2.5% mobile money/benki; 3% kadi. Ada za MNO zinaweza pia kumtozwa mlipaji. Ada zimeelezwa kuwa hazijumuishi kodi/levies. | Bei iliyochapishwa na provider; wastani, thibitisha offer ya merchant | [Fees](https://www.malipopay.co.tz/fees), [Pricing](https://malipopay.co.tz/pricing) |
| Mfano wa TZS 20,000 | Ada ya provider inayokadiriwa: TZS 500 kwa 2.5%, au TZS 600 kwa 3%, kabla ya kodi/ada nyingine. Mteja anaweza kuwa na ada ya operator kwa mobile money. | Hesabu kutoka kiwango kilichochapishwa; si quotation ya mwisho | Fees/Pricing hapo juu |
| Kadi | Card ina hatua tofauti ya intent, hosted session/fields, uthibitishaji wa server na webhook. Nyaraka zinaonyesha card activation setup fee ya TZS 150,000, lakini ukurasa huo huo unasema hakuna setup fee kwa ujumla. | Nyaraka za provider zinakinzana; chini hadi ithibitishwe | [Pricing FAQ](https://malipopay.co.tz/pricing), [Card API docs](https://developers.malipopay.co.tz/integration/card-payments/) |
| Settlement | Provider anasema mobile collections ni T+1; benki mara nyingi siku hiyo kabla ya cutoff; settlement kwenda akaunti yako ni bure. | Dai la provider; thibitisha kwa mkataba/merchant offer | [Pricing](https://malipopay.co.tz/pricing) |
| Go-live na limits | Docs zinaeleza merchant account hupitia KYC/approval; maelezo ya card yanaonyesha limit ya TZS 50,000 kwa transaction na daily starter cap kabla ya go-live approval. | Dai la docs; huenda likabadilika kwa merchant/project | [Card API docs](https://developers.malipopay.co.tz/integration/card-payments/) |
| Webhook | Signature ya HMAC-SHA256 imetajwa; nyaraka za webhook zinasema payment webhook haitumwi tena ikishindwa. Collection-flow page inasema retries 5. | Kinzani ndani ya docs rasmi; hatari ya production | [Webhook docs](https://developers.malipopay.co.tz/integration/webhooks/), [Collection flow](https://developers.malipopay.co.tz/guides/flows/payment-collection/) |
| Uthibitisho wa leseni | FAQ ya MALIPOPAY inadai Lockwood ni PSP yenye leseni ya BoT. Katika ripoti ya BoT ya 2025, jina “Lockwood” na “MALIPOPAY” halikupatikana kwenye Annex O niliyochunguza. | Dai la provider halijathibitishwa huru; haimaanishi moja kwa moja kuwa haina leseni | [Provider FAQ](https://malipopay.co.tz/faq), [BoT Annual Report 2025, Annex O](https://www.bot.go.tz/Publications/Regular/Annual%20Report/sw/2026060211494565.pdf) |

### Ushahidi unaopingana na mapengo

- **Webhook retries:** Collection-flow docs zinasema retries tano; Webhook docs zinasema malipo hayatumiwi tena baada ya delivery kushindwa. Mpaka provider athibitishe, tujenge reconciliation ya mara kwa mara kupitia status endpoint na tusitegemee webhook peke yake.
- **Ada ya card:** Pricing FAQ inataja ada ya activation ya TZS 150,000, lakini pia inasema hakuna setup fees. Pata quotation iliyoandikwa inayoonyesha kama ada hiyo inatumika kwa akaunti hii.
- **Leseni:** Taarifa ya Lockwood ya kuwa na leseni ni madai ya provider. BoT report ya 2025 haionyeshi jina hilo katika Annex O. Omba jina kamili la taasisi yenye leseni na namba ya leseni; kisha linganisha na BoT.
- **Mikataba na uendeshaji:** Sijathibitisha chargeback/refund timelines, minimum settlement, account freeze process, SLA ya support, tax invoice, au masharti ya reserve/hold.
- **API test:** Hakuna sandbox transaction iliyofanywa. Kuwepo kwa docs hakuthibitishi credentials, activation, au flow ya TCC itafanya kazi bila marekebisho.

**Msingi na kiwango cha uhakika:** Docs rasmi za provider na ripoti rasmi ya BoT. Ripoti ya BoT inaweza kuwa na jina la kisheria tofauti au kutokuwa na sasisho jipya zaidi; hitimisho la leseni limeachwa wazi.

## 3. Makes sense — Uhalisia wa chaguo

**Hali:** Inaendelea

| Kigezo | Tathmini | Ushahidi / dhana | Pengo / hatua |
|---|---|---|---|
| Urahisi kwa mlipaji | Inafaa: mobile money push na USSD zimeandikwa; card pia ipo | Docs rasmi za MALIPOPAY | Thibitisha channel zitakazowashwa kwa merchant wetu |
| Gharama | Inaonekana wazi: 2.5% mobile/bank; 3% card | Bei ya provider; kodi/MNO fees zinaweza kuongezeka | Pata quotation na ulinganishe na Selcom offer |
| Kufaa kwa TCC | Inawezekana kwa kiasi cha TZS 20,000 | 2.5% = TZS 500; 3% = TZS 600 kabla ya gharama nyingine | Thibitisha minimums, recurring collection, refunds na settlement |
| Uwezekano wa kiufundi | API inaeleweka, ila card ni flow tofauti na reconciliation inahitajika | Docs rasmi | Fanya test transaction, callback signature, duplicate event na missed webhook |
| Uaminifu / udhibiti | Bado haujathibitishwa kikamilifu kwa vyanzo huru | BoT report haikupata jina la Lockwood | Pata legal entity + license no. na uthibitishe na BoT kabla ya live |

**Uamuzi:** Jaribu kwanza kwenye sandbox baada ya due diligence; usihamishe live bado.

**Sababu na kinachoweza kubadilisha uamuzi:** Chaguo linavutia kwa coverage na API, lakini ushahidi wa leseni, ada ya card, na webhook behavior bado una mapengo. Uamuzi unaweza kubadilika baada ya majibu rasmi na test inayopita.

### Ulinganisho wa Selcom kwa TCC

Selcom Checkout docs zinaonyesha checkout iliyohostiwa inayoweza kuonyesha mobile money pull pamoja na debit/credit cards, na zina status/callback flow. Selcom pia imeorodheshwa kama Selcom Paytech Limited kwenye Annex O ya BoT Annual Report 2025. Hii ni ushahidi wa ziada wa kuwepo kwake kwenye orodha rasmi ya PSP katika ripoti hiyo; si uthibitisho wa huduma au bei ya mkataba wa TCC.

| Kigezo | Selcom | MALIPOPAY |
|---|---|---|
| Mobile money + card checkout | Zote zimeandikwa kwenye Selcom checkout docs | Mobile money/USSD na card zipo; card ina hatua zake tofauti |
| Bei ya wazi ya merchant | Sijapata rate card rasmi ya gateway inayoweza kulinganishwa; omba quotation ya TCC | 2.5% mobile/bank, 3% card imechapishwa; card setup fee ina mgongano wa maelezo |
| BoT public report | Selcom Paytech Limited imeorodheshwa katika Annex O ya 2025 | Lockwood/MALIPOPAY haikupatikana kwa jina hilo katika ripoti hiyo; omba legal entity/license number |
| Utekelezaji wa sasa TCC | Selcom adapter tayari ipo kwenye code ya TCC kulingana na hali ya mradi | Bado haijaunganishwa; itahitaji adapter, credentials na sandbox/live testing |
| Hatari ya callback | Hakiki callback + status endpoint kwa implementaion iliyopo | Docs zinakinzana kuhusu retry; reconciliation lazima iwepo |

**Uamuzi wa kulinganisha kwa hali ya sasa:** Selcom ndiyo chaguo la kuendelea nalo kwa TCC kwa sasa, kwa sababu iko kwenye BoT report tuliyokagua na integration tayari ipo kwenye code. Hatujathibitisha kuwa Selcom ni nafuu zaidi: uamuzi wa gharama unahitaji quotation yake ya maandishi. MALIPOPAY ibaki alternative ya majaribio kama itatoa uthibitisho wa jina la leseni, gharama zote na callback behavior.

## 4. Innovation — Suluhisho linalopendekezwa

**Hali:** Inasubiri uamuzi wa provider

Tumia provider interface moja ya TCC yenye adapter inayoweza kubadilishwa (Selcom/MALIPOPAY). Order/subscription logic na database ya TCC zibaki provider-neutral. Adapter ya MALIPOPAY ifanywe tu baada ya kuchagua provider; webhook ihakiki HMAC, iwe idempotent, ihifadhi delivery, kisha reconciliation worker ikague payments ambazo hazijapata final status.

**Hatari kuu:** Usimpe plan status “active” kwa kurudi tu kwa browser au kwa webhook isiyothibitishwa. Activate plan pale server inapothibitisha malipo ya mwisho kwa API ya provider au callback yenye signature sahihi.

**Hatua inayofuata:** Pata majibu yaliyoandikwa kuhusu leseni, kadi, webhook retries, na masharti ya malipo; baada ya hapo linganisha na Selcom na uchague.

## 5. Possible output — Kitu kinachoweza kutengenezwa

**Hali:** Inasubiri uamuzi; integration haijaanza.

- Output inayowezekana: adapter ya Laravel ya MALIPOPAY pamoja na sandbox flow, webhook, reconciliation, na admin payment logs.
- Mtumiaji wa kwanza: admin anasanidi credentials; developer/customer ananunua Plus kupitia mobile money au card.
- Mpaka wa MVP: collection, status confirmation, refund status handling, idempotency na reconciliation; hakuna payouts kwa developers.
- Credentials: merchant API token na webhook signing secret zihifadhiwe `.env`/server secrets pekee, kamwe frontend au GitHub repository.
- Kipimo cha kufaulu: test payment inakuwa pending kisha paid/failed kwa hali sahihi; invalid signature inakataliwa; repeated callback haiwashi subscription mara mbili; webhook iliyokosekana inapatikana kupitia reconciliation.

## Source ledger

| ID | Kichwa / mchapishaji | URL | Tarehe ya kuchapishwa / kusasishwa | Tarehe ya kufikiwa | Dai linaloungwa mkono | Mipaka |
|---|---|---|---|---|---|---|
| S1 | Payment Collection Flow / MALIPOPAY docs | https://developers.malipopay.co.tz/guides/flows/payment-collection/ | Updated Nov 15, 2026 | 2026-11-27 | Collection endpoint, USSD timeout, idempotency, fees fields, retries | Inakinzana na webhook docs kuhusu retry |
| S2 | Webhooks / MALIPOPAY docs | https://developers.malipopay.co.tz/integration/webhooks/ | Updated Nov 15, 2026 | 2026-11-27 | HMAC signature, no retry for payment events, status reconciliation | Inakinzana na S1 |
| S3 | Card Payments / MALIPOPAY docs | https://developers.malipopay.co.tz/integration/card-payments/ | Crawled Nov 2026 | 2026-11-27 | Card setup and flow; limits described in docs | Limits may depend on account approval |
| S4 | Fees / MALIPOPAY | https://www.malipopay.co.tz/fees | Crawled Nov 2026 | 2026-11-27 | Published collection/card fees and fee exclusions | Not a merchant-specific signed offer |
| S5 | Pricing / MALIPOPAY | https://malipopay.co.tz/pricing | Crawled Nov 2026 | 2026-11-27 | Published fees, settlement claims, card setup fee | Page contains potentially conflicting setup-fee statements |
| S6 | FAQ / MALIPOPAY | https://malipopay.co.tz/faq | Crawled Nov 2026 | 2026-11-27 | Supported channels and provider's licensing claim | Provider self-claim, not independent verification |
| S7 | National Payment Systems Annual Report 2025 / Bank of Tanzania, Annex O | https://www.bot.go.tz/Publications/Regular/Annual%20Report/sw/2026060211494565.pdf | 2025 report; published/crawled 2026 | 2026-11-27 | Official annex listing non-bank payment system providers; Lockwood/MALIPOPAY not found by text search | Absence in this report does not prove unlicensed status or exclude another legal name |
| S8 | Selcom Developers API Reference | https://developers.selcommobile.com/ | Crawled Nov 2026 | 2026-11-27 | Hosted checkout supporting mobile money pull and card; status/callback APIs | Docs do not provide TCC's signed pricing or account-specific activation limits |

## Historia ya maamuzi

| Tarehe | Hatua | Uamuzi / kilichobadilika | Ushahidi au sababu |
|---|---|---|---|
| 2026-11-27 | Utafiti | MALIPOPAY imeongezwa kama mgombea wa kuchunguzwa; hakuna provider switch iliyoidhinishwa | API/fees docs zinafaa kuchunguza, lakini due diligence bado haijakamilika |
| 2026-11-27 | Ulinganisho | Selcom inapendekezwa kubaki chaguo la sasa kwa TCC; MALIPOPAY ibaki alternative | Selcom iko kwenye ripoti ya BoT tuliyoangalia na adapter ipo; Selcom pricing bado inahitaji quotation |

## Maswali yaliyo wazi

1. Ni jina gani kamili la taasisi yenye leseni ya BoT, na namba ya leseni ni ipi?
2. Je, ada ya card ya TZS 150,000 inatumika kwa TCC? Je, kuna ada nyingine ya activation, monthly, reserve, refund au settlement?
3. Je, payment webhooks zinarudiwa au hazirudiwi? Ikiwa hazirudiwi, status endpoint na reconciliation cadence ipi inapendekezwa rasmi?
4. Je, mobile money push na card vinaweza kujaribiwa kabla ya go-live, na ni limits gani zitakuwa kwenye merchant account yetu?
5. Settlement inaingia akaunti gani, kwa muda gani, na kuna hold/reserve au minimum ya kutoa fedha?
