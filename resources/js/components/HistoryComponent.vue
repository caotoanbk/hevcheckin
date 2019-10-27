<template>
    <div class="container-fluid">
<div class="row mt-5">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">History</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Card Name</th>
                      <th>Employee Identity</th>
                      <th>Employee Name</th>
                      <th>Registered At</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="history in histories.data" :key="history.id">
                      <td>{{history.CardName}}</td>
                      <td>{{history.EmployeeIdentity}}</td>
                      <td>{{history.employee_name}}</td>
                      <td>{{history.created_at | myDate}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="histories"
                @pagination-change-page="getResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

    </div>

</template>

<script>
    export default {
        data() {
            return {
                histories: {},
            }
        },
        methods: {
            getResults(page = 1) {
              axios.get('api/history?page=' + page)
                  .then(response => {
                      this.histories = response.data;
                  });
            },
            loadHistories() {
                axios.get("api/history").then(({data}) => (this.histories = data));
            }
        },
        created() {
            Fire.$on('searching', () => {
                let query = this.$parent.search;
                axios.get('api/findHistory?q=' + query)
                .then((data) => {
                    this.users = data.data
                })
                .catch(() => {

                })
            })
            this.loadHistories();
            Fire.$on('AfterCreate', () => {
                this.loadHistories();
            });
            // setInterval(() => this.loadUsers(), 3000);
        }
    }
</script>
