<template>
    <div class="content">
        <div class="divtable border-top">
            <div class="divthead">
                <div class="divthead-row">
                    <div class="divthead-elem wf-45 text-center">SNo</div>
                    <div class="divthead-elem mwf-200 text-left">Job Title</div>
                    <div class="divthead-elem wf-350 text-left">Running Time</div>
                    <div class="divthead-elem wf-150 text-center">Status</div>
                    <div class="divthead-elem wf-200 text-center">Action</div>
                </div>
            </div>
            <div class="divtbody" v-if="records.length >= 1">
                <div class="divtbody-row" v-for="(record, rid) in records" :key="'dsg-'+record.id">
                    <div class="divtbody-elem wf-45 text-center">{{ rid+1 }}</div>
                    <div class="divtbody-elem mwf-200 text-left">{{ record.title }}</div>
                    <div class="divtbody-elem wf-350 text-left">
                        <span class="border-right d-inline-block text-center px-2">
                            <b class="fw-400 d-block">Month</b> 
                            <span class="pt-1 d-block">{{ record.month }}</span>
                        </span>
                        <span class="border-right d-inline-block text-center px-2">
                            <b class="fw-400 d-block">Day</b> 
                            <span class="pt-1 d-block">{{ record.day }}</span>
                        </span>
                        <span class="border-right d-inline-block text-center px-2">
                            <b class="fw-400 d-block">Hour</b> 
                            <span class="pt-1 d-block">{{ record.hour }}</span>
                        </span>
                        <span class="border-right d-inline-block text-center px-2">
                            <b class="fw-400 d-block">Minutes</b> 
                            <span class="pt-1 d-block">{{ record.minutes }}</span>
                        </span>
                        <span class="d-inline-block text-center px-2">
                            <b class="fw-400 d-block">Weekday</b> 
                            <span class="pt-1 d-block">{{ record.weekday }}</span>
                        </span>
                    </div>
                    <div class="divtbody-elem wf-150 text-center">
                        <span class="stage-och stage-10" v-if="record.status == 1">Active</span>
                        <span class="stage-och stage-1" v-else>Deactive</span>
                    </div>
                    <div class="divtbody-elem wf-200 text-center">
                        <button @click="editTask(record)" class="btn btn-sm btn-warning text-uppercase fw-500 p-t-2 p-b-2 fs-11 mr-1">Edit</button>
                        <button @click="deleteTask(record)" class="btn btn-sm btn-danger text-uppercase fw-500 p-t-2 p-b-2 fs-11">Delete</button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="alert text-center text-danger">No Data Found
                </div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="taskForm" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Cron Job</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label class="text-uppercase">Title</label>
                                    <input name="title" class="form-control" type="text" placeholder="Enter title" required v-model="form.title" :class="{ 'is-invalid': form.errors.has('title') }">
                                    <has-error :form="form" field="title"></has-error>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-12">
                                <div class="form-group">
                                    <label class="text-uppercase">Cron Command Line</label>
                                    <input name="command_line" class="form-control" type="text" placeholder="Enter command line" required v-model="form.command_line" :class="{ 'is-invalid': form.errors.has('command_line') }">
                                    <has-error :form="form" field="command_line"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-group">
                                    <label class="text-uppercase">Month</label>
                                    <input name="month" class="form-control" type="text" placeholder="Enter month number" required v-model="form.month" :class="{ 'is-invalid': form.errors.has('month') }">
                                    <has-error :form="form" field="month"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-group">
                                    <label class="text-uppercase">Day</label>
                                    <input name="day" class="form-control" type="text" placeholder="Enter Day (0 - 31)" required v-model="form.day" :class="{ 'is-invalid': form.errors.has('day') }">
                                    <has-error :form="form" field="day"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-group">
                                    <label class="text-uppercase">Hour</label>
                                    <input name="hour" class="form-control" type="text" placeholder="Enter Hour" required v-model="form.hour" :class="{ 'is-invalid': form.errors.has('hour') }">
                                    <has-error :form="form" field="hour"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-group">
                                    <label class="text-uppercase">Minutes</label>
                                    <input name="minutes" class="form-control" type="text" placeholder="Enter Minutes" required v-model="form.minutes" :class="{ 'is-invalid': form.errors.has('minutes') }">
                                    <has-error :form="form" field="minutes"></has-error>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label class="text-uppercase">Weekdays</label>
                                    <input name="weekdays" class="form-control" type="text" placeholder="Enter Day number (0 for sunday, 6 for saturday)" required v-model="form.weekdays" :class="{ 'is-invalid': form.errors.has('weekdays') }">
                                    <has-error :form="form" field="weekdays"></has-error>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-group">
                                    <label class="text-uppercase">Status</label>
                                    <select name="status" class="form-control" v-model="form.status" id="status">
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                    <has-error :form="form" field="month"></has-error>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="updateTask">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            records:{},
            record:'',
            form: new Form({
                id:'',
                title:'',
                command_line:'',
                hour:'',
                minutes:'',
                day:'',
                month:'',
                weekday:'',
                status:''
            })
        }
    },
    methods: {
        getTasks() {
            this.$Progress.start()
            axios.get('/api/tasks').then((response) => {
                this.records = response.data
                this.$Progress.finish()
            }).catch((error) => {
                console.log(error.message)
            })
        },
        editTask(task) {
            this.form.fill(task);
            $('#taskForm').modal('show')
        },
        updateTask() {
            this.form.put('/api/tasks/'+this.form.id).then((response) => {
                Vue.$toast.success('Cron Job updated successfully!!')
                this.form.reset()
                this.getTasks()
                $('#taskForm').modal('hide')
            }).catch((error) => {
                console.log(error.message)
            })
        },
        deleteTask(task_id) {
            this.$swal({
                title: 'Are you sure ? ',
                text: "You want to delete this cron ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.form.delete('/api/tasks/'+task_id).then((response) => {
                        Vue.$toast.success('Cron Job deleted successfully!!')
                        this.getTasks()
                    }).catch((error) => {
                        console.log(error.message)
                    })
                }
            })
        }
    },
    created() {
        this.getTasks()
    }
    
}
</script>