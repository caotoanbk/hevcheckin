<template>
    <div class="container-fluid">
<div class="row mt-5">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">User</h3>

                <div class="card-tools">
                    <button class="btn btn-success" @click="newModal">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Type</th>
                      <th>Registered At</th>
                      <th>Picture</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                      <td>{{user.id}}</td>
                      <td>{{user.name}}</td>
                      <td>{{user.email}}</td>
                      <td>{{user.type | upText}}</td>
                      <td>{{user.created_at | myDate}}</td>
                      <td><img class="img" v-bind:src="'/img/profile/' + user.photo" style="max-width: 100px;"></td>
                      <td>
                          <a href="#" @click="editModal(user)">
                              <i class="fa fa-edit blue"></i>
                          </a>
                          /
                          <a href="#" @click="deleteUser(user.id)">
                              <i class="fa fa-trash red"></i>
                          </a>

                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="users"
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
        <h5 class="modal-title" v-show="!editmode" id="addNewModalLabel">Add New</h5>
        <h5 class="modal-title" v-show="editmode">Update User's Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form @submit.prevent = "editmode ? updateUser() : createUser()">
      <div class="modal-body">
        <div class="form-group">
          <input v-model="form.name" type="text" name="name"
            class="form-control" placeholder="Name" :class="{ 'is-invalid': form.errors.has('name') }">
          <has-error :form="form" field="name"></has-error>
        </div>
       <div class="form-group">
          <input v-model="form.email" type="text" email="email"
            class="form-control" placeholder="Email Address" :class="{ 'is-invalid': form.errors.has('email') }">
          <has-error :form="form" field="email"></has-error>
        </div>
       <div class="form-group">
          <textarea v-model="form.bio" bio="bio"
            class="form-control" placeholder="Short bio for user (Optional)" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
          <has-error :form="form" field="bio"></has-error>
        </div>

        <div class="form-group">
            <select name="type" v-model="form.type" id="type" class="form-control" :class="{'is-invalid': form.errors.has('type') }">
                <!-- <option value="">Select User Role</option>
                <option value="admin">Admin</option>
                <option value="user">Standard user</option>
                <option value="author">Author</option> -->
                <option v-for="option in options">{{option}}</option>
            </select>
            <has-error :form="form" field="type"></has-error>
        </div>

       <div class="form-group">
          <input v-model="form.password" type="text" password="password"
            class="form-control" placeholder="Password" :class="{ 'is-invalid': form.errors.has('password') }">
          <has-error :form="form" field="password"></has-error>
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
        data() {
            return {
                editmode: false,
                users: {},
                form: new Form({
                    id:'',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: ''
                }),
                options: ['one', 'two', 'three']
            }
        },
        methods: {
            changePicture(e){
                let file = e.target.files[0];
                let reader = new FileReader();

                if(file['size'] < 2111775){ 
                    reader.onload = function(e) {
                      $('#blah').attr('src', e.target.result);
                    }               
                    reader.onloadend = (file) => {
                        // console.log('Result', reader)
                        this.form.photo = reader.result;
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
              axios.get('api/user?page=' + page)
                  .then(response => {
                      this.users = response.data;
                  });
            },
            updateUser() {
                this.$Progress.start();
                this.form.put('api/user/'+this.form.id)
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
            editModal(user){
                this.editmode = true;
                this.form.reset();
                $('input[type=file]').val('');
                $('#blah').addClass('d-none');
                $('#addNewModal').modal('show');
                this.form.fill(user);
                $('#blah').attr('src', '/img/profile/' + user.photo);
                $('#blah').removeClass('d-none');
            },
            newModal() {
                this.editmode = false;
                this.form.reset();
                $('input[type=file]').val('');
                $('#blah').addClass('d-none');
                $('#addNewModal').modal('show');
            },
            deleteUser(id) {
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

                      this.form.delete('api/user/'+id).then( () => {            
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
            loadUsers() {
                axios.get("api/user").then(({data}) => (this.users = data));
            },
            createUser(){
                this.$Progress.start();
                this.form.post('api/user')
                    .then(() => {
                        Fire.$emit('AfterCreate');
                        $('#addNewModal').modal('hide');
                        toast.fire({
                          type: 'success',
                          title: 'User Created successfully'
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
                axios.get('api/findUser?q=' + query)
                .then((data) => {
                    this.users = data.data
                })
                .catch(() => {

                })
            })
            this.loadUsers();
            Fire.$on('AfterCreate', () => {
                this.loadUsers();
            });
            // setInterval(() => this.loadUsers(), 3000);
        }
    }
</script>
