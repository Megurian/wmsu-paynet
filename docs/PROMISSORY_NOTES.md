# Promissory Note Feature Manual

This guide explains how the Promissory Note feature works in WMSU PayNet as it is implemented today. It is written for students, student coordinators, cashiers, and support staff who need to understand the full workflow from issuance to settlement and delinquency.

## What The Feature Does

The Promissory Note (PN) feature lets a student defer eligible unpaid mandatory fees while still completing the enrollment workflow. The note is tied to a specific student and enrollment, carries a due date, and moves through a controlled set of states until it is signed, approved, settled, or expired.

The feature is intentionally split across the existing student, coordinator, and cashier screens. There is no separate payment system for PN; it rides on the same cashiering architecture already used for regular fee collection.

## Who Uses It

| Role | What they do |
| :--- | :--- |
| Student | View note status, download the template, sign it, upload the signed copy, and track the balance. |
| Student Coordinator | Issue the note, review uploads, approve or reject signatures, clear enrollment, and monitor reports. |
| Treasurer / Cashier | Detect an active PN during student lookup and collect deferred fees against the note. |
| College Org Cashier | Use the same settlement flow as the treasurer, but only within the organization scope. |
| System | Void expired unsigned notes, send reminders, advance delinquency status, refresh enrollment financial status, and log key actions. |

## Entry Points

| Screen / Action | Route or View |
| :--- | :--- |
| Student PN list | `/student/promissory-notes` and `resources/views/student/promissory_notes.blade.php` |
| Student template download | `/student/promissory-notes/{note}/download` |
| Student signed upload | `/student/promissory-notes/{note}/sign` |
| Coordinator PN queue | `/college/promissory-notes` and `resources/views/college/promissory_notes_approval.blade.php` |
| Coordinator PN reports | `/college/promissory-notes/dashboard` and `/college/promissory-notes/export` |
| Treasurer lookup | `/treasurer/cashiering/student/{student}/promissory-notes` |
| Treasurer collection | `/treasurer/cashiering/collect` |
| Organization lookup | `/college_org/students/{student}/promissory-notes` |
| Organization collection | `/college_org/payment/collect` |
| Coordinator issue note | `/college/students/{student}/promissory-notes` |

## Status Lifecycle

| Status | Meaning | Who can act |
| :--- | :--- | :--- |
| `PENDING_SIGNATURE` | The note was created, but the student has not uploaded a signed copy yet. | Student downloads, signs, and uploads. Coordinator can re-issue if needed. |
| `PENDING_VERIFICATION` | The student uploaded a signed copy and the coordinator must review it. | Coordinator approves or rejects. |
| `ACTIVE` | The note is approved and can be used for clearance and settlement. | Cashier can collect payment. Student can see the active note. |
| `VOIDED` | The note expired unsigned or was otherwise voided. Linked fees are detached. | No settlement. Coordinator may issue a replacement note if policy allows. |
| `CLOSED` | The note was fully settled. | No further action unless a new note is needed later. |
| `DEFAULT` | The note passed the semester-end delinquency check while still unpaid. | Cashier can still collect; coordinator monitors. |
| `BAD_DEBT` | The note remained unpaid through the academic-year check. | Cashier can still collect; next-semester enrollment remains blocked until settled. |

## End-To-End Workflow

1. The student coordinator opens the verify-payment workflow and creates a PN for unpaid mandatory fees.
2. The note is created in `PENDING_SIGNATURE` state and becomes visible in the student's PN page.
3. The student downloads the template, prints it, signs it, and uploads the signed copy.
4. The upload changes the note to `PENDING_VERIFICATION` and notifies the coordinator.
5. The coordinator opens the approval queue, reviews the uploaded file, and either approves it to `ACTIVE` or rejects it back to `PENDING_SIGNATURE`.
6. Once active, the cashier can settle the note during regular payment collection.
7. When the balance reaches zero, the note closes.
8. If the student misses the signature deadline, the separate signature deadline command `promissory-notes:check-signature-deadline` voids the note and notifies the student.
9. If the student misses the due date, the delinquency command `promissory-notes:process-delinquency` advances the note to `DEFAULT` at semester end, then later to `BAD_DEBT` at school year end.

## Student Flow

### What the student sees

The student PN page shows:

- A status counter for each note state.
- The note ID, original amount, remaining balance, signature deadline, and issuer.
- A deferred-fees table showing which fees are attached to the note.
- Action buttons that change based on status.

### What to do when the note is `PENDING_SIGNATURE`

1. Open the PN page at `/student/promissory-notes`.
2. Click `Download Template`.
3. Print the PDF, sign it physically, and prepare the file for upload.
4. Click `Upload Signed Note` and choose a valid file. Accepted formats are `pdf`, `jpg`, `jpeg`, or `png`.
5. Wait for the coordinator review message. The note should move to `PENDING_VERIFICATION`.

### What to do when the note is `PENDING_VERIFICATION`

No student action is needed. The note is waiting for the coordinator to approve or reject the upload.

### What to do when the note is `ACTIVE`, `DEFAULT`, or `BAD_DEBT`

The page remains read-only for collection status. The student should contact the cashier if payment must be made, or the coordinator if there is a dispute about the note.

## Coordinator Flow

### 1. Issue the note

The coordinator issues the note from the verify-payment modal, not from a separate stand-alone issuance screen. The modal only shows the `Create Promissory Note` action when the student has unpaid mandatory fees and is eligible for PN handling. The issued due date must fall within the current active semester window, and only unpaid mandatory fees may be deferred onto the note.

### 2. Review the uploaded note

The coordinator opens `/college/promissory-notes`, filters by `Pending Review`, and views the uploaded document inline. The approval form includes a confirmation checkbox and an optional notes field.

### 3. Approve or reject

- Approve moves the note to `ACTIVE`.
- Reject sends the note back to `PENDING_SIGNATURE` and clears the uploaded file association so the student can re-submit.

### 4. Clear enrollment

The coordinator clears the student for enrollment only when the financial context allows it. The controller no longer forces the enrollment status to `PAID`; it uses the separate financial status field.

## Cashier Flow

The cashier flow is the same for college fees and organization fees.

1. Search or select the student.
2. The system checks whether the student has an active PN in the current college scope.
3. If a PN exists, the UI shows the deferred-fees section alongside normal fees.
4. The cashier chooses the fees to settle and enters the cash received.
5. If the note is selected, the settlement service records a PN payment, writes fee-payment pivots, and updates the remaining balance.
6. If the note reaches zero balance, the status becomes `CLOSED`.

### Important cashier rules

- The treasurer route only accepts college-scoped notes.
- The organization route only accepts notes belonging to the organization's college.
- Cash-only payments still work when no PN is present.
- Overpayment returns change; it is not added to the PN balance.
- Concurrent payment attempts are protected by a row lock.

## Enrollment And Financial Status

The system separates academic enrollment status from financial status.

| Field | Purpose |
| :--- | :--- |
| `status` | Academic state such as `NOT_ENROLLED`, `FOR_PAYMENT_VALIDATION`, `ENROLLED`, and related progression states. |
| `financial_status` | Financial state such as `PAID`, `PARTIALLY_PAID`, `DEFERRED`, `DEFAULT`, or `BAD_DEBT`. |

The clearance workflow uses financial status, not the academic status column, to decide whether a student can proceed.

## Delinquency And Deadlines

### Signature deadline

Unsigned notes are voided automatically after the signature deadline passes. The scheduled command `php artisan promissory-notes:check-signature-deadline` runs daily and voids any `PENDING_SIGNATURE` notes whose `signature_deadline` has passed. When a note is voided, the linked fees are detached so they are not stranded on a dead note.

### Due-date reminders

The delinquency service can send reminders before the due date, then mark the note overdue when the semester ends.

### Escalation ladder

1. `ACTIVE` note passes due date during the semester-end check -> `DEFAULT`.
2. `DEFAULT` note remains unpaid until academic-year end -> `BAD_DEBT`.
3. `BAD_DEBT` remains collectible. It blocks next-semester enrollment, but it does not block payment settlement.

### How Escalation Is Triggered

Escalation happens when the delinquency logic is executed. This logic can be triggered in two ways:

#### Automatic Scheduler Trigger
- **When**: Every day via the Linux cron job running `php artisan schedule:run` every minute.
- **What it runs**: `ProcessPromissoryNoteDelinquency` console command → `PromissoryNoteDelinquencyService::processDelinquency()`.
- **Result**: All escalation checks happen daily without manual action.

#### Manual Admin Trigger
- **When**: OSA (Office of Student Affairs) administrator manually ends a semester in the system.
- **Where**: UI action at `OSASetupController::endSemester()`.
- **What it runs**: Directly calls `PromissoryNoteDelinquencyService::processDelinquency(now())` as part of the semester-ending transaction.
- **Result**: Escalations are processed immediately when the semester is ended, without waiting for the next scheduler run.

### Escalation Conditions

Each trigger evaluates these conditions:

#### ACTIVE → DEFAULT (Semester End)
- **Condition**: Current date reaches or exceeds the active semester's effective end date **AND** the note's due date has passed.
- **Which column**: The process uses `Semester::effectiveEndDate()`, which checks in this priority order:
  1. `ended_at` (actual end date, set when OSA manually ends the semester)
  2. `will_end_at` (planned end date, set during semester creation)
  3. `school_years.sy_end` (fallback to school year end)
- **Checked by**: `shouldDefaultNotes()` method in `PromissoryNoteDelinquencyService`.
- **Effect**: Note moves to `DEFAULT` status; cashier and coordinator continue to monitor it.

#### DEFAULT → BAD_DEBT (School Year End)
- **Condition**: Current date reaches or exceeds the school year's `sy_end` date.
- **Checked by**: `shouldPromoteToBadDebt()` method in `PromissoryNoteDelinquencyService`.
- **Effect**: Note moves to `BAD_DEBT` status; enrollment blocks for the next semester until settled.

### Testing Delinquency Manually

For testing or data backfilling, you can run the delinquency command manually from the terminal. This is useful for verifying that notes correctly transition states (e.g., from `ACTIVE` to `DEFAULT`) without waiting for the actual calendar date.

#### Basic Execution
Runs the delinquency check using the current system time:
```powershell
php artisan promissory-notes:process-delinquency
```

#### Time Travel (Testing)
To simulate the command running on a specific date (e.g., to trigger a due-date transition that hasn't happened yet), use the `--as-of` option:
```powershell
php artisan promissory-notes:process-delinquency --as-of="2026-12-31"
```
The `--as-of` option accepts any valid date string. This will process reminders and state transitions as if it were that date.

## Production Assurance Checklist

The delinquency command can be tested manually with `php artisan promissory-notes:process-delinquency`, but production confidence depends on the scheduler, queue workers, and logging around it. Before go-live, verify these items:

1. The server has a cron entry that runs `php artisan schedule:run` every minute.
2. The queue worker is running continuously if `QUEUE_CONNECTION` is not `sync`, so queued PN notifications are actually delivered in the background.
3. `routes/console.php` still registers both `promissory-notes:check-signature-deadline` and `promissory-notes:process-delinquency` on the scheduler.
4. The application logs show daily `Voided ... expired unsigned promissory note(s).` and `Processed promissory note delinquency` entries after the scheduler runs.
5. A staging or pre-production smoke test is done with at least one overdue note to confirm the status transition and notification path.
6. The team knows where to check failed jobs, queue worker health, and the app log when the schedule stops running.

### What To Watch For After Deployment

- If the delinquency log line stops appearing, the scheduler is probably not being triggered by cron.
- If status transitions happen but notifications do not arrive, the command may be running while the queue worker is down.
- If a manual run works but production does not, compare the production `.env` values for `QUEUE_CONNECTION`, mail configuration, and `APP_URL`.
- If you need a stronger audit trail, add a heartbeat or last-successful-run timestamp to the app so the admin team can see when the job last completed.

## Reporting

The coordinator reporting dashboard shows:

- Total issued notes.
- Total collected amount.
- Total remaining balance.
- Overdue notes.
- Defaulted notes.
- Closed notes.
- Voided notes.

The report page also allows CSV export for the currently selected school year and semester.

## Common Rules And Guardrails

- Only one active or pending PN should exist per student at a time.
- A PN must belong to the same college scope as the student being processed.
- A PN can only be created for unpaid mandatory fees.
- A note may settle while `ACTIVE`, `DEFAULT`, or `BAD_DEBT`, provided balance remains.
- The system does not allow direct status editing as a normal user action; status changes happen through the note services and controller actions.

## Common Messages And What They Mean

| Message | Meaning |
| :--- | :--- |
| `This promissory note can no longer be signed.` | The note is already voided, closed, or past the signing rules. |
| `Promissory note does not belong to this student.` | The selected note and student do not match. |
| `Promissory note does not belong to this college.` | The selected note is outside the user's scope. |
| `Student is not financially clearable yet.` | The note or fee coverage does not satisfy the current clearance rules. |
| `Promissory note is locked for update.` | Another cashier is already processing the same note. Retry after a moment. |

## Where To Look In The Code

If you need to confirm how the feature behaves, start with these files:

- [Student portal controller](../app/Http/Controllers/Student/StudentPortalController.php)
- [Coordinator controller](../app/Http/Controllers/CoordinatorController.php)
- [Treasurer cashiering controller](../app/Http/Controllers/TreasurerCashieringController.php)
- [Organization payment controller](../app/Http/Controllers/OrganizationPaymentController.php)
- [OSA setup controller](../app/Http/Controllers/OSASetupController.php)
- [Promissory note model](../app/Models/PromissoryNote.php)
- [Settlement service](../app/Services/PromissoryNoteSettlementService.php)
- [Issuance service](../app/Services/PromissoryNoteIssuanceService.php)
- [Delinquency service](../app/Services/PromissoryNoteDelinquencyService.php)
- [Check signature deadline command](../app/Console/Commands/CheckSignatureDeadlineCommand.php)
- [Student PN view](../resources/views/student/promissory_notes.blade.php)
- [Coordinator approval view](../resources/views/college/promissory_notes_approval.blade.php)
- [Coordinator report view](../resources/views/college/promissory_notes_dashboard.blade.php)

## Short Operational Summary

The PN feature is a controlled deferment workflow. Students sign a note, coordinators approve it, cashiers collect against it, the system tracks remaining balances, and the scheduler advances overdue notes through delinquency states. If you understand those five actions, you understand the feature.