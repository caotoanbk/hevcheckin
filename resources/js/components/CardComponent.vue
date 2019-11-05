<template>
<div class="container">
    <div class="row mt-2">
      <div class="col-md-12">
        <input type="text" v-model="card_query" class="form-control mb-2" placeholder="search card..." @keyup="findCard()">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title" v-if="type == 'avaiable'">Avaiable Card <span class="badge badge-success p-2">{{cards.total}}</span></h3>
            <h3 class="card-title" v-else-if="type =='allocated'">Allocated Card <span class="badge badge-warning p-2">{{cards.total}}</span></h3>
            <h3 class="card-title" v-else>All Card <span class="badge badge-primary p-2">{{cards.total}}</span></h3>

            <div class="card-tools">
                <button class="btn btn-success btn-sm" @click="newModal" v-show="this.$parent.currentUser.SupplierName === null">Add New Card</button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Card Name</th>
                  <th>Ecode</th>
                  <th>Ename</th>
                  <th>Action</th>
                  <th>Công ty</th>
                  <th>Modify</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="card in cards.data" :key="card.CardName">
                  <td>{{card.created_at | myDate}}</td>
                  <td>{{card.CardName}}</td>
                  <td>{{card.employee?card.employee.EmployeeCode:''}}</td>
                  <td>{{card.employee?card.employee.EmployeeName:''}}</td>
                  <td>{{card.employee?'Đang sử dụng':'Spare'}}</td>
                  <td>{{card.employee?card.employee.SupplierName:''}}</td>
                  <td>
                      <a href="#" @click="editModal(card)">
                          <i class="fa fa-edit blue"></i>
                      </a>
                      /
                      <a href="#" @click="deleteCard(card.CardName)">
                          <i class="fa fa-trash red"></i>
                      </a>

                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <pagination :data="cards"
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
            <h5 class="modal-title" v-show="!editmode" id="addNewModalLabel">Add New Card</h5>
            <h5 class="modal-title" v-show="editmode">Update Card's Usage</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form @submit.prevent = "editmode ? updateCard() : createCard()">
          <div class="modal-body">
            <div class="form-group">
              <input v-model="form.CardName" type="text" :readonly = "editmode ? true : false" name="name"
                class="form-control" placeholder="Card Name" :class="{ 'is-invalid': form.errors.has('CardName') }">
              <has-error :form="form" field="CardName"></has-error>
            </div>

            <div class="form-group">
                <select name="EmployeeCode" v-model="form.EmployeeCode" id="EmployeeCode" class="form-control" :class="{'is-invalid': form.errors.has('EmployeeCode') }">
                    <option value="" v-if="editmode">Trả thẻ</option>
                    <option value="" v-else>Select employee</option>
                    <option v-for="op in employee_options" :value="op.EmployeeCode">{{op.EmployeeName}}</option>
                </select>
                <has-error :form="form" field="EmployeeCode"></has-error>
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
                cards: {},
                form: new Form({
                    CardName: '',
                    EmployeeCode: ''
                }),
                card_query: '',
                employee_options: {},
            }
        },
        methods: {
            getEmployeeOptions(){
                axios.get('api/getEmployeeOptions')
                    .then( (response) => {
                        this.employee_options = response.data;
                        console.log(response.data);
                    })
            },
            getEmployeeOptionsEdit(id){
                axios.get('api/getEmployeeOptionsEdit/'+id)
                    .then( (response) => {
                        this.employee_options = response.data;
                        console.log(response.data);
                    })
            },
            getResults(page = 1) {
                axios.get('api/card?type='+this.type+'&page=' + page)
                    .then(response => {
                        this.cards = response.data;
                    });
            },
            updateCard() {
                this.$Progress.start();
                this.form.put('api/card/'+this.form.CardName)
                .then(() => {
                    //success
                    $('#addNewModal').modal('hide');
                    swal.fire(
                      'Updated!',
                      'Information has been updated.',
                      'success'
                    )
                    this.$Progress.finish();
                    Fire.$emit('AfterCreateCard');
                })
                .catch(() => {
                    this.$Progress.fail();
                });
            },
            editModal(card){
                this.editmode = true;
                this.form.reset();
                this.getEmployeeOptionsEdit(card.CardName);
                $('#addNewModal').modal('show');
                this.form.fill(card);
            },
            newModal() {
                this.editmode = false;
                this.form.reset();
                this.getEmployeeOptions();
                $('#addNewModal').modal('show');
            },
            deleteCard(id) {
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

                      this.form.delete('api/card/'+id).then( () => {            
                            swal.fire(
                              'Deleted!',
                              'Your file has been deleted.',
                              'success'
                            )
                            Fire.$emit('AfterCreateCard');
                      }).catch( () => {
                        swal("Failed!", "There was something wrong.", "warning");
                      })
                    }
                })
            },
            findCard() {
                axios.get('api/findCard?type='+this.type+'&q=' + this.card_query)
                    .then((data) => {
                        this.cards = data.data
                    })
                    .catch(() => {

                    })
            },
            loadCards() {
                this.$parent.search = '';
                axios.get("api/card?type="+this.type).then(({data}) => (this.cards = data));
            },
            createCard(){
                this.$Progress.start();
                this.form.post('api/card')
                    .then(() => {
                        Fire.$emit('AfterCreateCard');
                        $('#addNewModal').modal('hide');
                        toast.fire({
                          type: 'success',
                          title: 'Card Created successfully'
                        });
                        this.$Progress.finish();
                    })
                    .catch(() => {

                    });
            }
        },
        created() {
            // Fire.$on('searching', () => {
            //     let query = this.$parent.search;
            //     axios.get('api/findCard?type='+this.type+'&q=' + query)
            //     .then((data) => {
            //         this.cards = data.data
            //     })
            //     .catch(() => {

            //     })
            // })
            this.loadCards();
            Fire.$on('AfterCreateCard', () => {
                this.loadCards();
            });
        },
        watch: {
            type: function(newVal, oldVal){
                this.loadCards();
            }
        }
    }
</script>
