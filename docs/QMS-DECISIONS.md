# QMS Decision Log

**Purpose.** The QMS specification and user manual describe many capabilities whose *configuration* is the agency's decision, not the developer's. This file records those decisions so that:

1. nothing gets built against a guessed answer;
2. the decision record itself becomes QMS evidence (an assessor will ask *who decided this, and when*);
3. defaults are explicit, so an override later is a change of decision rather than a silent drift.

**Rule:** no code is written for an item marked `BLOCKING` until its status is `DECIDED`. Items marked `DEFAULT OK` are built against the stated default and corrected in settings later.

**Source documents:**
- `QMS_Ready_Recruitment_Agency_System_Specification.docx` v1.0 (15 Nov 2026)
- `LANDAS_Updated_User_Manual_QMS_Integrated_V3.docx` v3.0 (15 Nov 2026)

**Statuses:** `OPEN` · `DECIDED` · `DEFERRED` · `DEFAULT OK`

| # | Item | Blocks | Status | Owner | Decision |
|---|------|--------|--------|-------|----------|
| D-01 | Approval matrix (preparer/reviewer/approver, per document type) | QMS-10 | OPEN | Agency management | — |
| D-02 | Record retention periods per record type | all retention code | OPEN | Compliance / legal | — |
| D-03 | Quality objectives, KPI definitions, targets, reporting periods | Quality Objectives module (not yet carded) | OPEN | Management | — |
| D-04 | Risk scoring scale (likelihood/impact bands) | QMS-11 | DEFAULT OK | QMS Manager | 1–5 × 1–5; low ≤4, medium 5–9, high 10–14, critical ≥15 |
| D-05 | NC severity classification (observation / minor / major) | QMS-15 | DEFAULT OK | QMS Manager | Defaults as named in spec §14; editable in settings |
| D-06 | CAPA closure rule (verification + effectiveness required) | QMS-15/16 | DEFAULT OK | QMS Manager | Both required before closure; no override (spec §26) |
| D-07 | Document requirements by destination / job category | Document compliance rules | OPEN | Processing / compliance staff | — |
| D-08 | Complaint acknowledgment & resolution service targets | QMS-13 | DEFAULT OK | Operations | ack 2 days, resolve 15 days; per-agency override |
| D-09 | Document review frequency defaults | QMS-06 | DEFAULT OK | QMS Manager | Annual; per-document override |
| D-10 | Audit criteria & internal audit schedule | QMS-17 | OPEN | Agency management | — |
| D-11 | Training responsibilities & competency requirements | Training module (not yet carded) | OPEN | HR / Training Officer | — |
| D-12 | Welfare escalation & emergency procedures | Welfare module | OPEN | Operations | — |
| D-13 | Privacy notice text and lawful basis for processing | Data privacy work | OPEN | Agency (legal) | — |
| D-14 | Legal hold representation | Retention code | OPEN | Compliance / legal | — |
| D-15 | Who owns the "never say DMW-certified" positioning rule | Governance | OPEN | Agency management | Spec §28 / Manual App. C wording is authoritative |

---

## Notes on the blocking items

### D-01 — Approval matrix
Spec §19 requires segregation of duties. The build needs the *shape*, not just the names.
Recommended default if no decision is made: **3-step (preparer → reviewer → approver)**, self-approval blocked, matrix editable per document type.
Agency must confirm: 2-step or 3-step; who fills each seat; whether it varies by document type; whether self-approval is ever permitted and how it is logged as an exception.

### D-02 — Retention periods
Spec §24: *"Do not apply a universal period to all record types."*
This is the item where a wrong answer is unrecoverable — deleting applicant records too early is a legal problem, not a bug.
Needed per record type: applicants, recruitment processing, contracts/deployment, accounting, welfare/cases, QMS records. Also: what starts the clock, who approves a disposal, what disposal evidence is kept, how legal hold is represented.
**Do not default this to a number to make progress.**

### D-07 — Document requirements
Varies by destination country, job category, agency type, and current DMW issuances. The manual requires validation against the latest official issuances before any checklist is treated as a compliance rule.

---

## Standing constraints (not decisions — already fixed)

These come from the documents and are not open for configuration without amending the source documents:

1. **No compliance claims.** Never describe LANDAS as "DMW-approved", "DMW-certified", "guaranteed DMW-compliant" or "ISO-certified" unless such approval has actually been granted and is evidenced. (Spec §28, Manual App. C.)
2. **Sample targets are illustrative.** Figures like 98% record completeness and 95% document verification are examples, never mandatory DMW targets.
3. **Dashboards must be traceable.** Every metric must resolve to underlying records, with its reporting period and last-refresh shown. Sample values must never be presented as live results. (Spec §17, §26.)
4. **Approved records are never silently overwritten.** Prior versions are retained with history. (Spec §2, §10.2.)
5. **Audit events are immutable.** Append-only; no update or delete through ordinary application functions. (Spec §26.)
6. **CAPA closure requires verification *and* effectiveness review.** (Spec §26.)
