---
name: org-identity-fields-admin-only
description: department_id, staff_category, position_id are admin-only — self-service must never write them
metadata:
  type: project
---

A user's `department_id`, `staff_category`, and `position_id` are a **security boundary**: they drive Forms leadership/VC routing (`Position.category` = hod/dean/director, office membership), so a user who can edit their own could self-assign a leadership position and capture VC referrals / approvals.

Rules:
- Self-service profile (`HomeController::updateUserInfo`, `admin/settings.blade.php`) must **never** accept or write these three fields. Settings shows them read-only/locked.
- Only an institutional admin may change them — via `HomeController::updateUserDetails` (`users.update-details`, the admin "Edit Info" modal in `admin/users.blade.php`, which also edits email) and at creation in `storeUser`. The whole `#users` route block is gated by the `institutional_admin` middleware (server-side, not just sidebar hiding).
- `register()` self-signup sets them but `is_approve = false`; the admin reviews/corrects before approving.

**Why:** user flagged that letting a user edit their own position is a privilege-escalation vector. **How to apply:** any new profile-edit surface or new sensitive identity field gets the same treatment — admin-gated mutation, read-only for the owner. "Admin" here = institutional admin (DB role `user` / `is_admin=false`) per the reversed role terminology.
