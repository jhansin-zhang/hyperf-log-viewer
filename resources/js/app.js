function toggleGroup(groupId, header) {
    var filesDiv = document.getElementById(groupId);
    var icon = header.querySelector('.toggle-icon');
    
    if (filesDiv.style.display === 'none') {
        filesDiv.style.display = 'block';
        icon.textContent = '▼';
    } else {
        filesDiv.style.display = 'none';
        icon.textContent = '▶';
    }
}
