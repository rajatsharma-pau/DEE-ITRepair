DEE IT Repair - Corrected Storekeeper Vendor Estimate Workflow

Workflow now implemented:
1. Employee submits request. Every request goes first to Storekeeper.
2. Storekeeper opens request and enters:
   - Vendor from vendor master
   - Estimate amount
   - Estimate date
   - Estimate details
   - Upload estimate/quotation file
3. Storekeeper can forward the selected estimate to either:
   - Programmer, for computer/IT verification, or
   - Store Incharge, for non-computer/store-related verification/approval remarks.
4. Programmer / Store Incharge records:
   - Receive for verification
   - Estimate OK
   - Estimate Not OK
   - Need Revised Estimate
   - Verification remarks and work/observation
5. After Estimate OK, Storekeeper can generate Financial Sanction Proforma.
6. Storekeeper prints proforma and submits manually to D-4.
7. D-4 can mark manual file received in the system.
8. Storekeeper records sanction received/rejected, completes work, and closes after employee confirmation.

Important:
- Store Incharge is handled through Employee Charges, not only designation. Any Assistant Professor/Professor/employee can be Store Incharge by adding active charge "Store Incharge" in employee profile.
- Vendor estimate/quotation is required before forwarding to Programmer or Store Incharge.
- Proforma cannot be generated until Programmer or Store Incharge marks estimate as OK.
