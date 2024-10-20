
document.querySelectorAll('.like-button').forEach(button => {
button.addEventListener('click', async function() {
const postId = this.getAttribute('data-post-id');
const heartIcon = this.querySelector('.fas.fa-heart');
const likeCountSpan = this.querySelector('.like-count');

try {
    const response = await fetch('./backend/likes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId }),
    });

    const result = await response.json();

    if (response.ok) {
        if (result.message === 'Like adicionado.') {
            heartIcon.classList.add('liked'); // Adiciona classe liked
        } else if (result.message === 'Like removido.') {
            heartIcon.classList.remove('liked'); // Remove classe liked
        }
        likeCountSpan.textContent = result.like_count; // Atualiza a contagem de likes
    } else {
        console.error('Erro ao curtir o post:', result.error);
    }
} catch (error) {
    console.error('Erro na requisição:', error);
}
});
});
    

document.getElementById('post-form').addEventListener('submit', async (event) => {
    event.preventDefault(); // Evita o comportamento padrão do formulário

    const content = document.querySelector('textarea[name="content"]').value; // Captura o valor do textarea

    if (!content.trim()) { // Verifica se o conteúdo não está vazio
        alert("Por favor, escreva algo no post.");
        return;
    }

    const response = await fetch('./backend/create_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ content }), // Envia o conteúdo como JSON
    });

    const result = await response.json(); // Espera a resposta como JSON

    if (response.ok) {
        console.log('Post criado com sucesso:', result);
        window.location.reload(); // Recarrega a página
    } else {
        console.error('Erro ao criar o post:', result.error);
    }
});

document.querySelector('.comment-button').forEach(button => 
    button.addEventListener('click', function() {
    const postId = this.getAttribute('data-post-id');
    console.log(postId);
    window.location.href = 'post.php?id=' + postId; // redireciona para a página post.php com os parâmetros
  })

);

