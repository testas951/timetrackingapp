const times = document.getElementById('times');

if (times) {
    times.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-time') {
            if (confirm('Are you sure?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/times/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}