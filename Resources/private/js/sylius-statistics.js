import $ from 'jquery';
import drawChart from './sylius-chart';

class StatisticsComponent {
  constructor(wrapper) {
    if (!wrapper) return;

    this.wrapper = wrapper;
    this.chart = null;
    this.chartCanvas = this.wrapper.querySelector('#stats-graph');
    this.summaryBoxes = this.wrapper.querySelectorAll('[data-stats-summary]');
    this.buttons = this.wrapper.querySelectorAll('[data-stats-button]');
    this.loader = this.wrapper.querySelector('.stats-loader');

    this.buttons.forEach(button => button.addEventListener('click', this.fetchData.bind(this)));
    this.init();
  }

  init() {
    const labels = this.chartCanvas.getAttribute('data-labels') || '[]';
    const values = this.chartCanvas.getAttribute('data-values') || '[]';
    const currency = this.chartCanvas.getAttribute('data-currency') || '';

    this.chart = drawChart(this.chartCanvas, JSON.parse(labels), JSON.parse(values), { prefix: currency });
  }

  fetchData(e) {
    const date = new Date();
    let interval = e.target.getAttribute('data-stats-button');
    let startDate;
    let endDate;

    switch (interval) {
      case 'year':
        startDate = new Date(date.getFullYear(), 0, 1);
        endDate = new Date(date.getFullYear() + 1, 0, 0);
        interval = 'month';
        break;
      case 'month':
        startDate = new Date(date.getFullYear(), date.getMonth(), 1);
        endDate = new Date(date.getFullYear(), date.getMonth() + 1, 1);
        interval = 'day';
        break;
      case 'week':
        startDate = new Date(date.getTime() - 604800000);
        endDate = new Date(date.getTime() + 604800000);
        interval = 'day';
        break;
    }

    var url = e.target.getAttribute('data-stats-url') +
      '&interval=' + interval +
      '&startDate=' + this.formatDate(startDate) +
      '&endDate=' + this.formatDate(endDate);

    if (url) {
      this.toggleLoadingState(true);

      $.ajax({
        type: 'GET',
        url,
        dataType: 'json',
        accept: 'application/json'
      }).done((response) => {
        this.updateSummaryValues(response.statistics);
        this.updateButtonsState(e.target);
        this.updateGraph(response.sales_summary);
      }).always(() => {
        this.toggleLoadingState(false);
      });
    }
  }

  updateSummaryValues(data) {
    this.summaryBoxes.forEach((box) => {
      const name = box.getAttribute('data-stats-summary');
      if (name in data) {
        box.innerHTML = data[name];
      }
    });
  }

  updateGraph(data) {
    this.chart.data.labels = data.months;
    this.chart.data.datasets[0].data = data.sales;
    this.chart.update();
  }

  updateButtonsState(activeButton) {
    this.buttons.forEach(button => button.classList.remove('active'));
    activeButton.classList.add('active');
  }

  toggleLoadingState(loading) {
    if (loading) {
      this.loader.classList.add('active');
    } else {
      this.loader.classList.remove('active');
    }
  }

  formatDate(date) {
    let month = `${(date.getMonth() + 1)}`;
    let day = `${date.getDate()}`;
    const year = `${date.getFullYear()}`;

    if (month.length < 2) month = `0${month}`;
    if (day.length < 2) day = `0${day}`;

    return [year, month, day].join('-');
  }
}

export default StatisticsComponent;
