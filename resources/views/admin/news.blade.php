<x-layouts.dashboard :navmenu="$navmenu">


<div class="modal fade" id="editNewsModal" tabindex="-1" aria-labelledby="editNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Edit News</h4>
            </div>
            <div class="modal-body">
                <form id="editNewsForm" class="needs-validation" novalidate method="post" action="/dashboard/news/save" enctype="multipart/form-data">
@csrf
                    <input type="hidden" id="NewsId" name="id">

                    <div class="mb-3 text-center">
                        <img id="previewImage" src="" alt="No Image" class="img-thumbnail" style="width: 400px; height: 250px; object-fit: fill;">
                    </div>

                    <div class="mb-3">
                        <label for="NewsImage" class="form-label">News Image</label>
                        <p class="text-primary" style="font-size: smaller; font-style:italic">Max 1mb, jpg/jpeg only, 512x512</p>
                        <input type="file" class="form-control" id="NewsImage" name="image" accept="image/*" required>
                        <div class="invalid-feedback">Please upload a file.</div>
                    </div>

                    <div class="mb-3">
                        <label for="NewsVideo" class="form-label">News Video</label>
                        <p class="text-primary" style="font-size: smaller; font-style:italic">Instagram Reel Link (Optional)</p>
                        <p class="text-dark d-inline" style="font-size: smaller;">Example : </p>
                        <p class="text-danger d-inline" style="font-size: smaller;">https://www.instagram.com/reel/DJRP_Ajzd9V/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA==</p>

                        <input type="text" class="form-control" id="NewsVideo" name="video">
                    </div>

                    <div class="mb-3">
                        <label for="NewsTitle" class="form-label">News Title</label>
                        <input type="text" class="form-control" id="NewsTitle" name="title" required>
                        <div class="invalid-feedback">Please enter your title.</div>
                    </div>

                    <div class="mb-3">
                        <label for="NewsDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="NewsDescription" name="description" rows="6" required></textarea>
                        <div class="invalid-feedback">Please enter your description.</div>
                    </div>

                    <div class="mb-3">
                        <label for="NewsCategory" class="form-label">News Category</label>
                        <select class="form-select" id="NewsCategory" name="category" required>
                            @foreach ($categoryList as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--begin::Container-->
<div class="container-fluid">
    <!--begin::Row-->
    <div class="row g-4">
        <div class="col-12 text-end mb-3">
            <button onclick="changeTitle('Create News',true)" class="btn btn-success edit-News">Create new News</button>
        </div>
        <div class="col-12">
            <div class="callout callout-info">
                Use the Create new news to upload your news with accurate information.
            </div>
        </div>

        <div class="row justify-content-start mt-4">
            <div class="col-4">
                <select class="form-select" onchange="location = 'news/filter/' + this.value;">
                    <option value="All" {{ ($activefilter === 'All') ? 'selected' : '' }}>All</option>
                    @foreach ($categoryList as $category)
                        <option value="{{ $category }}" {{ ($activefilter === $category) ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @foreach ($news as $article)
            <div class="col-md-3 col-sm-4 col-12">
                <div class="card m-1 h-100">
                    <img class="card-img-top" src="/assets/uploads/{{ $article['image'] }}" style="object-fit: cover;" alt="Card image cap">
                    <div class="card-body">
                        <p class="card-text"><b>{{ $article['title'] }}</b></p>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <div class="btn-group" role="group">
                            <button onclick="changeTitle('Edit News',false)" class="btn btn-warning mx-1 edit-News"
                                data-id="{{ $article['id'] }}"
                                data-title="{{ $article['title'] }}"
                                data-description="{{ $article['description'] }}"
                                data-image="{{ $article['image'] }}"
                                data-video="{{ isset($article['video']) ? $article['video'] : '' }}"
                                data-category="{{ $article['category'] }}">
                                Edit
                            </button>
                            <button onclick="deleteConfirmation({{ $article['id'] }})" class="btn btn-danger mx-1">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12">
            {{ $pager->links() }}
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".edit-News").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();

                document.getElementById("NewsId").value = this.getAttribute("data-id");
                document.getElementById("NewsTitle").value = this.getAttribute("data-title");
                document.getElementById("NewsDescription").value = this.getAttribute("data-description");
                document.getElementById("NewsCategory").value = this.getAttribute("data-category");
                document.getElementById("NewsVideo").value = this.getAttribute("data-video");

                let imageSrc = "/assets/uploads/" + this.getAttribute("data-image");
                document.getElementById("previewImage").src = imageSrc;

                let modal = new bootstrap.Modal(document.getElementById("editNewsModal"));
                modal.show();
            });
        });

        document.getElementById("NewsImage").addEventListener("change", function(event) {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("previewImage").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<script>
    function changeTitle(newTitle, isCreate) {
        let titleField = document.getElementById("modalTitle");
        let input = document.getElementById("NewsImage");

        if (titleField) {
            titleField.innerText = newTitle;
        }

        if (input) {
            let invalidFeedback = input.nextElementSibling;

            if (isCreate) {
                input.setAttribute("required", "required");
            } else {
                input.removeAttribute("required");
            }

            if (invalidFeedback && invalidFeedback.classList.contains("invalid-feedback")) {
                invalidFeedback.style.display = isCreate ? "" : "none";
            }

            if (!isCreate) {
                input.classList.remove("is-invalid");
            }
        }
    }


    function deleteConfirmation(id) {
        let deleteUrl = "/dashboard/news/delete/" + id;

        if (confirm("Are you sure you want to delete this News?")) {
            window.location.href = deleteUrl;
        }
    }
</script>
</x-layouts.dashboard>