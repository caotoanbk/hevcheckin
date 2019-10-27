<template>
    <div class="container-fluid">
<div class="row mt-5">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title" v-if="type == 'allocated'">Allocated Employee <span class="badge badge-success p-2">{{employees.total}}</span></h3>
                <h3 class="card-title" v-else-if="type == 'avaiable'">Unallocated Employee <span class="badge badge-warning p-2">{{employees.total}}</span></h3>
                <h3 class="card-title" v-else>All Employee <span class="badge badge-primary p-2">{{employees.total}}</span></h3>


                <div class="card-tools">
                    <button class="btn btn-success btn-sm" @click="newModal">Add New Employee</button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Employee Name</th>
                      <th>Employee Identity</th>
                      <th>Employee Info</th>
                      <th>Employee Type</th>
                      <th>Employee Cardname</th>
                      <th>Registered At</th>
                      <th>Employee Picture</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="employee in employees.data" :key="employee.id">
                      <td>{{employee.id}}</td>
                      <td>{{employee.EmployeeName}}</td>
                      <td>{{employee.EmployeeIdentity}}</td>
                      <td>{{employee.EmployeeInfo}}</td>
                      <td>{{employee.EmployeeType}}</td>
                      <td>{{employee.EmployeeCardname}}</td>
                      <td>{{employee.created_at | myDate}}</td>
                      <td><img class="img" v-bind:src="'/img/profile/' + employee.EmployeePhoto" style="max-height: 60px;"></td>
                      <td>
                          <a href="#" @click="editModal(employee)">
                              <i class="fa fa-edit blue"></i>
                          </a>
                          /
                          <a href="#" @click="deleteEmployee(employee.id)">
                              <i class="fa fa-trash red"></i>
                          </a>

                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="employees"
                @pagination-change-page="getResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>


<!-- Modal -->
<div class="modal fade" id="addNewModal" tabindex="-1" role="dialog" aria-labelledby="addNewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" v-show="!editmode" id="addNewModalLabel">Add New Employee</h5>
        <h5 class="modal-title" v-show="editmode">Update Employee's Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form @submit.prevent = "editmode ? updateEmployee() : createEmployee()">
      <div class="modal-body">
        <div class="form-group">
          <input v-model="form.EmployeeName" type="text" name="EmployeeName"
            class="form-control" placeholder="Name" :class="{ 'is-invalid': form.errors.has('EmployeeName') }">
          <has-error :form="form" field="EmployeeName"></has-error>
        </div>
         <div class="form-group">
          <input v-model="form.EmployeeIdentity" type="text"
            class="form-control" :readonly="editmode ? true : false" placeholder="CMND/ID" :class="{ 'is-invalid': form.errors.has('EmployeeIdentity') }">
          <has-error :form="form" field="EmployeeIdentity"></has-error>
        </div>
       <div class="form-group">
          <input v-model="form.EmployeeInfo" type="text"
            class="form-control" placeholder="Info" :class="{ 'is-invalid': form.errors.has('EmployeeInfo') }">
          <has-error :form="form" field="EmployeeInfo"></has-error>
        </div>
        <div class="form-group">
            <select name="EmployeeType" v-model="form.EmployeeType" id="type" class="form-control" :class="{'is-invalid': form.errors.has('EmployeeType') }">
                <option value="Công nhân thời vụ">Công nhân thời vụ</option>
                <option value="Công nhân chính thức">Công nhân chính thức</option>
            </select>
            <has-error :form="form" field="EmployeeType"></has-error>
        </div>

        <div class="form-group">
            <select name="EmployeeCardname" v-model="form.EmployeeCardname" id="EmployeeCardname" class="form-control" :class="{'is-invalid': form.errors.has('EmployeeCardname') }">
                <option value="">Select Card</option>
                <option v-for="op in card_options" :value="op.CardName">{{op.CardName}}</option>
            </select>
            <has-error :form="form" field="EmployeeCardname"></has-error>
        </div>


        <div class="form-group">
            <label for="photo">Picture:</label>
            <input type="file" @change="changePicture" accept=".gif,.jpg,.jpeg,.png" id="imgInp">
        </div>
        <div class="pt-2">
            <img class="d-none" id="blah" src="#" alt="your image" style="max-height: 150px;" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button v-show="editmode" type="submit" class="btn btn-primary">Update</button>
        <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
      </div>
  </form>

    </div>
  </div>
</div>
    </div>

</template>

<script>
    export default {
        props: ['type'],
        data() {
            return {
                editmode: false,
                employees: {},
                form: new Form({
                    id:'',
                    EmployeeName: '',
                    EmployeeInfo: '',
                    EmployeeType: '',
                    EmployeeIdentity: '',
                    EmployeeCardname: '',
                    EmployeePhoto: ''
                }),
                card_options: {}
            }
        },
        methods: {
            getCardOptionsEdit(id){
                axios.get('api/getCardOptionsEdit/'+id)
                    .then( (response) => {
                        this.card_options = response.data;
                    })
            },
            getCardOptions(){
                axios.get('api/getCardOptions')
                    .then( (response) => {
                        this.card_options = response.data;
                    })
            },
            changePicture(e){
                let file = e.target.files[0];
                let reader = new FileReader();

                if(file['size'] < 2111775){ 
                    reader.onload = function(e) {
                      $('#blah').attr('src', e.target.result);
                    }               
                    reader.onloadend = (file) => {
                        // console.log('Result', reader)
                        this.form.EmployeePhoto = reader.result;
                    }

                    $('#blah').removeClass('d-none');
                    reader.readAsDataURL(file);
                }else{
                    swal.fire(
                      'Oops...',
                      'You are uploading a large file',
                      'error'
                    )
                }
            },
            getResults(page = 1) {
              axios.get('api/employee?type='+this.type+'&page=' + page)
                  .then(response => {
                      this.employees = response.data;
                  });
            },
            updateEmployee() {
                this.$Progress.start();
                this.form.put('api/employee/'+this.form.id)
                .then(() => {
                    //success
                    $('#addNewModal').modal('hide');
                    swal.fire(
                      'Updated!',
                      'Information has been updated.',
                      'success'
                    )
                    this.$Progress.finish();
                    Fire.$emit('AfterCreate');
                })
                .catch(() => {
                    this.$Progress.fail();
                });
            },
            editModal(employee){
                this.editmode = true;
                this.form.reset();
                this.getCardOptionsEdit(employee.id);
                $('input[type=file]').val('');
                $('#blah').addClass('d-none');
                $('#addNewModal').modal('show');
                this.form.fill(employee);
                $('#blah').attr('src', '/img/profile/' + employee.EmployeePhoto);
                $('#blah').removeClass('d-none');
            },
            newModal() {
                this.editmode = false;
                this.form.reset();
                this.form.EmployeeType = 'Công nhân thời vụ';
                this.getCardOptions();
                $('input[type=file]').val('');
                $('#blah').addClass('d-none');
                $('#addNewModal').modal('show');
            },
            deleteEmployee(id) {
                swal.fire({
                  title: 'Are you sure?',
                  text: "You won't be able to revert this!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, delete it!'
                }).then((result) => {

                    if(result.value){

                      this.form.delete('api/employee/'+id).then( () => {            
                            swal.fire(
                              'Deleted!',
                              'Your file has been deleted.',
                              'success'
                            )
                            Fire.$emit('AfterCreate');
                      }).catch( () => {
                        swal("Failed!", "There was something wrong.", "warning");
                      })
                    }
                })
            },
            loadEmployees() {
                axios.get("api/employee?type="+this.type).then(({data}) => (this.employees = data));
            },
            createEmployee(){
                this.$Progress.start();
                this.form.post('api/employee')
                    .then(() => {
                        Fire.$emit('AfterCreate');
                        $('#addNewModal').modal('hide');
                        toast.fire({
                          type: 'success',
                          title: 'Employee Created successfully'
                        });
                        this.$Progress.finish();
                    })
                    .catch(() => {

                    });
            }
        },
        created() {
            Fire.$on('searching', () => {
                let query = this.$parent.search;
                axios.get('api/findEmployee?type='+this.type+'&q=' + query)
                .then((data) => {
                    this.employees = data.data
                })
                .catch(() => {

                })
            })
            this.loadEmployees();
            Fire.$on('AfterCreate', () => {
                this.loadEmployees();
            });
            // setInterval(() => this.loadUsers(), 3000);
        },
        watch: {
            type: function(newVal, oldVal){
                this.loadEmployees();
            }
        }
    }
</script>
