<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_no')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('repair_category_id')->nullable();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('problem_template_id')->nullable();
            $table->unsignedBigInteger('selected_estimate_id')->nullable();

            // Every request first goes to Storekeeper. Current handler is only workflow state.
            $table->unsignedBigInteger('assigned_to_employee_id')->nullable();
            $table->string('current_handler_role')->default('storekeeper'); // storekeeper/programmer/d4_seat/employee/director/none

            // Verification/action persons
            $table->unsignedBigInteger('storekeeper_verified_by')->nullable();
            $table->unsignedBigInteger('programmer_verified_by')->nullable();
            $table->unsignedBigInteger('d4_received_by')->nullable();

            // Item details
            $table->string('item_type')->nullable();
            $table->string('item_name')->nullable();
            $table->string('inventory_no')->nullable();
            $table->string('room_no')->nullable();
            $table->enum('priority', ['Normal', 'Urgent'])->default('Normal');
            $table->text('problem_description');
            $table->string('attachment')->nullable();
            $table->string('status')->default('Submitted to Storekeeper');

            // Remarks / technical track
            $table->text('storekeeper_remarks')->nullable();
            $table->text('programmer_work_done')->nullable();
            $table->text('programmer_remarks')->nullable();
            $table->enum('programmer_estimate_status', ['Pending', 'Estimate OK', 'Estimate Not OK', 'Need Revised Estimate'])->default('Pending');
            $table->text('d4_remarks')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->text('employee_feedback')->nullable();

            // Financial sanction proforma fields, based on DEE paper proforma.
            $table->boolean('requires_financial_sanction')->default(false);
            $table->decimal('financial_sanction_amount', 12, 2)->nullable();
            $table->text('financial_sanction_purpose')->nullable();
            $table->string('purchase_payment_type')->nullable(); // purchase/payment/material/repair
            $table->string('vehicle_no')->nullable(); // kept for proforma format; can be item ref/inventory no
            $table->string('scheme_name')->nullable();
            $table->text('enclosure_details')->nullable();
            $table->date('proforma_date')->nullable();
            $table->unsignedBigInteger('proforma_generated_by')->nullable();
            $table->timestamp('proforma_generated_at')->nullable();
            $table->timestamp('proforma_printed_at')->nullable();
            $table->timestamp('d4_submitted_at')->nullable();
            $table->timestamp('d4_received_at')->nullable();
            $table->enum('manual_sanction_status', ['Not Submitted', 'Submitted to D-4', 'Received at D-4', 'Sanction Received', 'Rejected'])->default('Not Submitted');
            $table->string('signed_sanction_file')->nullable();

            // Timeline fields
            $table->timestamp('storekeeper_received_at')->nullable();
            $table->timestamp('storekeeper_verified_at')->nullable();
            $table->timestamp('forwarded_to_programmer_at')->nullable();
            $table->timestamp('programmer_received_at')->nullable();
            $table->timestamp('programmer_completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('repair_category_id')->references('id')->on('repair_categories')->onDelete('set null');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('set null');
            $table->foreign('problem_template_id')->references('id')->on('problem_templates')->onDelete('set null');
            $table->foreign('assigned_to_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('storekeeper_verified_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('programmer_verified_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('d4_received_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('proforma_generated_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_requests');
    }
}
