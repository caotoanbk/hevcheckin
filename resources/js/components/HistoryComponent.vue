<template>
    <div class="container-fluid">
<div class="row mt-2">
          <div class="col-md-12">
            <input type="text" v-model="history_query" class="form-control mb-2" placeholder="search history..." @keyup="findHistory()">
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
                      <th>Employee Name</th>
                      <th>Supplier Name</th>
                      <th>Registered At</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="history in histories.data" :key="history.id">
                      <td>{{history.CardName}}</td>
                      <td>{{history.EmployeeName}}</td>
                      <td>{{history.SupplierName}}</td>
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
                history_query: ''
            }
        },
        methods: {
            getResults(page = 1) {
              axios.get('api/history?page=' + page)
                  .then(response => {
                      this.histories = response.data;
                  });
            },
            findHistory() {
                axios.get('api/findHistory?q=' + this.history_query)
                .then((data) => {
                    this.histories = data.data
                })
                .catch(() => {

                })
            },
            loadHistories() {
                axios.get("api/history").then(({data}) => (this.histories = data));
            }
        },
        created() {
            // Fire.$on('searching', () => {
            //     let query = this.$parent.search;
            //     axios.get('api/findHistory?q=' + query)
            //     .then((data) => {
            //         this.users = data.data
            //     })
            //     .catch(() => {

            //     })
            // })
            this.loadHistories();
            Fire.$on('AfterCreate', () => {
                this.loadHistories();
            });
            // setInterval(() => this.loadUsers(), 3000);
        }
    }
</script>
