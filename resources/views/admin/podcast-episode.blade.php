<x-layouts.dashboard :navmenu="$navmenu">


<div class="modal fade" id="editEpisodeModal" tabindex="-1" aria-labelledby="editEpisodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Edit Episode</h4>
            </div>
            <div class="modal-body">
                <form id="editEpisodeForm" class="needs-validation" novalidate method="post" action="/dashboard/podcast/episode/save" enctype="multipart/form-data">
@csrf
                    <input type="hidden" id="EpisodeId" name="id">
                    <input type="hidden" id="ContentId" name="id_content" value="{{ $id_content }}">
                    <div class="mb-3">
                        <label for="EpisodeAudio" class="form-label">Spotify Link</label>
                        <div class="d-flex">
                            <p class="text-dark" style="font-size: smaller;">Example : </p>
                            <p class="text-danger" style="font-size: smaller;">https://open.spotify.com/episode/loremipsumdolorsitamet</p>
                        </div>
                        <input type="text" class="form-control" id="EpisodeAudio" name="audio" required>
                        <div class="invalid-feedback">Please enter your audio.</div>
                    </div>


                    <div class="mb-3">
                        <label for="EpisodeTitle" class="form-label">Episode Title</label>
                        <input type="text" class="form-control" id="EpisodeTitle" name="title" required>
                        <div class="invalid-feedback">Please enter your title.</div>
                    </div>

                    <div class="mb-3">
                        <label for="EpisodeDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="EpisodeDescription" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback">Please enter your description.</div>
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
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-column flex-md-row justify-content-center align-items-center text-center text-md-start">
                    <!-- Gambar -->
                    <img src="/assets/uploads/{{ $podcast['image'] }}"
                        style="width:200px; height:200px; object-fit:cover; margin-bottom: 20px; margin-right: 0;"
                        class="me-md-3 mb-3 mb-md-0"
                        alt="Podcast Image">

                    <!-- Teks -->
                    <div>
                        <h1 class="mb-1">{{ $podcast['title'] }}</h1>
                        <p class="mb-0">{{ $podcast['description'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-between mb-3">
            <a href="/dashboard/podcast/open/{{ $id_content }}" class="btn btn-primary">Back to Podcast</a>
            <button onclick="changeTitle('Create Episode',true)" class="btn btn-success edit-episode">Create new Episode</button>
        </div>


        <div class="col-12">
            <div class="callout callout-info">
                Use the Create new episode to upload your Episode episode with accurate information.
            </div>
        </div>
        @foreach ($episodes as $episode)
            <div class="col-12 mb-1">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <iframe
                            src="{{ $episode['audio'] }}"
                            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                            style="width: 100%; height: 200px; border-radius: 8px; border: none;"></iframe>

                        <p class="card-text">{{ $episode['description'] }}</p>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            <button onclick="changeTitle('Edit Episode',false)" class="btn btn-warning mx-1 edit-episode"
                                data-id="{{ $episode['id'] }}"
                                data-title='{{ $episode['title'] }}'
                                data-description="{{ $episode['description'] }}"
                                data-audio="{{ $episode['audio'] }}">
                                Edit
                            </button>
                            <button onclick="deleteConfirmation({{ $episode['id'] }})" class="btn btn-danger mx-1">Delete</button>
                        </div>
                    </div>
                </div>

            </div>
    </div>
@endforeach

<div class="col-12">
    {{ $pager->links() }}
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".edit-episode").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();

                document.getElementById("EpisodeId").value = this.getAttribute("data-id");
                document.getElementById("EpisodeTitle").value = this.getAttribute("data-title");
                document.getElementById("EpisodeDescription").value = this.getAttribute("data-description");
                document.getElementById("EpisodeAudio").value = this.getAttribute("data-audio");

                let modal = new bootstrap.Modal(document.getElementById("editEpisodeModal"));
                modal.show();
            });
        });
    });
</script>

<script>
    function changeTitle(newTitle, isCreate) {
        let titleField = document.getElementById("modalTitle");
        let input = document.getElementById("EpisodeImage");

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
        let deleteUrl = "/dashboard/podcast/episode/delete/" + id;

        if (confirm("Are you sure you want to delete this Episode?")) {
            window.location.href = deleteUrl;
        }
    }
</script>

</x-layouts.dashboard>