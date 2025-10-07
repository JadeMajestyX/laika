  new Chart(document.getElementById('chartCitas'), {
    type: 'pie',
    data: {
      labels: ['Consulta general', 'Esterilización', 'Cirugía', 'Urgencias'],
      datasets: [{ data: [40,25,20,15], backgroundColor: ['#6f42c1','#dc3545','#0d6efd','#20c997'] }]
    }
  });

  new Chart(document.getElementById('chartMascotas'), {
    type: 'pie',
    data: {
      labels: ['Perros','Gatos','Aves','Reptiles','Roedores'],
      datasets: [{ data: [50,30,10,5,5], backgroundColor: ['#6f42c1','#198754','#ffc107','#0d6efd','#dc3545'] }]
    }
  });

  new Chart(document.getElementById('chartIngresos'), {
    type: 'bar',
    data: {
      labels: ['Ene','Feb','Mar','Abr','May','Jun'],
      datasets: [{ label:'Ingresos en $', data:[8500,9200,8700,9600,10300,9900], backgroundColor:'#6f42c1' }]
    }
  });

  new Chart(document.getElementById('chartProductos'), {
    type: 'bar',
    data: {
      labels: ['Alimento perro','Antipulgas','Arena gatos','Juguetes','Shampoo'],
      datasets: [{ label:'Unidades vendidas', data:[90,70,60,50,40], backgroundColor:'#28a745' }]
    },
    options: { indexAxis: 'y' }
  });