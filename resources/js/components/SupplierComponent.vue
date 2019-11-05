<template>
    <div class="container-fluid">
<div class="row mt-2">
          <div class="col-md-12">
            <input type="text" v-model="supplier_query" class="form-control mb-2" placeholder="search supplier..." @keyup="findSupplier()">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Suppliers</h3>

                <div class="card-tools">
                    <button class="btn btn-success btn-sm" @click="newModal">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Name</th>
                      <th>Info</th>
                      <th>Card Range</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                      <td>{{supplier.created_at | myDate}}</td>
                      <td>{{supplier.SupplierName}}</td>
                      <td>{{supplier.SupplierInfo}}</td>
                      <td>{{supplier.SupplierCardRange}}</td>
                      <td>
                          <a href="#" @click="editModal(supplier)">
                              <i class="fa fa-edit blue"></i>
                          </a>
                          /
                          <a href="#" @click="deleteSupplier(supplier.id)">
                              <i class="fa fa-trash red"></i>
                          </a>

                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="suppliers"
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
        <h5 class="modal-title" v-show="!editmode" id="addNewModalLabel">Add New Supplier</h5>
        <h5 class="modal-title" v-show="editmode">Update Supplier's Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form @submit.prevent = "editmode ? updateSupplier() : createSupplier()">
      <div class="modal-body">
        <div class="form-group">
          <input v-model="form.SupplierName" type="text" name="SupplierName"
            class="form-control" placeholder="Name" :class="{ 'is-invalid': form.errors.has('SupplierName') }">
          <has-error :form="form" field="SupplierName"></has-error>
        </div>
       <div class="form-group">
          <textarea v-model="form.SupplierInfo" name="SupplierInfo"
            class="form-control" placeholder="Supplier Info (Optional)" :class="{ 'is-invalid': form.errors.has('SupplierInfo') }"></textarea>
          <has-error :form="form" field="SupplierInfo"></has-error>
        </div>
        <div class="form-group">
          <input v-model="form.SupplierCardRange" type="text" name="SupplierCardRange"
            class="form-control" placeholder="Card range separate by comma. Eg: HL001,HL100" :class="{ 'is-invalid': form.errors.has('SupplierCardRange') }">
          <has-error :form="form" field="SupplierCardRange"></has-error>
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
        data() {
            return {
                editmode: false,
                suppliers: {},
                form: new Form({
                    id:'',
                    SupplierName: '',
                    SupplierInfo: '',
                    SupplierCardRange: ''
                }),
                supplier_query: ''
            }
        },
        methods: {
            getResults(page = 1) {
              axios.get('api/supplier?page=' + page)
                  .then(response => {
                      this.suppliers = response.data;
                  });
            },
            updateSupplier() {
                this.$Progress.start();
                this.form.put('api/supplier/'+this.form.id)
                .then(() => {
                    //success
                    $('#addNewModal').modal('hide');
                    swal.fire(
                      'Updated!',
                      'Information has been updated.',
                      'success'
                    )
                    this.$Progress.finish();
                    Fire.$emit('AfterCreateSupplier');
                })
                .catch(() => {
                    this.$Progress.fail();
                });
            },
            editModal(supplier){
                this.editmode = true;
                this.form.reset();
                $('#addNewModal').modal('show');
                this.form.fill(supplier);
            },
            newModal() {
                this.editmode = false;
                this.form.reset();
                $('#addNewModal').modal('show');
            },
            deleteSupplier(id) {
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

                      this.form.delete('api/supplier/'+id).then( () => {            
                            swal.fire(
                              'Deleted!',
                              'Your file has been deleted.',
                              'success'
                            )
                            Fire.$emit('AfterCreateSupplier');
                      }).catch( () => {
                        swal("Failed!", "There was something wrong.", "warning");
                      })
                    }
                })
            },
            findSupplier() {
                axios.get('api/findSupplier?q=' + this.supplier_query)
                .then((data) => {
                    this.suppliers = data.data
                })
                .catch(() => {

                })
            },
            loadSuppliers() {
                axios.get("api/supplier").then(({data}) => (this.suppliers = data));
            },
            createSupplier(){
                this.$Progress.start();
                this.form.post('api/supplier')
                    .then(() => {
                        Fire.$emit('AfterCreateSupplier');
                        $('#addNewModal').modal('hide');
                        toast.fire({
                          type: 'success',
                          title: 'Supplier Created successfully'
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
            //     axios.get('api/findSupplier?q=' + query)
            //     .then((data) => {
            //         this.suppliers = data.data
            //     })
            //     .catch(() => {

            //     })
            // })
            this.loadSuppliers();
            Fire.$on('AfterCreateSupplier', () => {
                this.loadSuppliers();
            });
            // setInterval(() => this.loadUsers(), 3000);
        }
    }
</script>
