import React, {useState, useEffect } from 'react';
import { Line, Bubble } from 'react-chartjs-2';
import styles from './styles.module.scss';
import Typography from '@material-ui/core/Typography';

import api from '../../api/methods';

import Button from '@material-ui/core/Button';
import ButtonGroup from '@material-ui/core/ButtonGroup';
import Tooltip from '@material-ui/core/Tooltip';
import Spinner from '../../helpers/spinner';

const optionsParbaudamie = ({ dates, views, label, bg, border }) => ({
	labels: dates,
	datasets: [
		{
			label,
			fill: false,
			backgroundColor: bg,
			borderColor: border,
			data: views
		}
	]
});
/*
const optionsBubble = ({ labels, views }) => ({
	labels: ['Svētdiena'],
	options: {
	  legend: {
		display: false
	  },
	  elements: {
		point: {
		  radius: function(context) {
			var index = context.dataIndex;
			var data = context.dataset.data[index];
			var size = context.chart.width;
			var base = data.value / 100;
			return (size / 40) * base;
		  }
		}
	  },
	  scales: {
		yAxes: [
		  {
			ticks: {
			  min: 0,
			  max: 8,
			  padding: 3 * 0.4,
			  callback: function(value, index, values) {
				return days[index];
			  }
			},
			position: "left"
		  }
		],
		xAxes: [
		  {
			ticks: {
			  beginAtZero: true,
			  min: 0,
			  max: 23,
			  stepSize: 2,
			  callback: function(value, index, values) {
				if (value % 2 === 0) {
				  return value + ":00";
				}
			  }
			}
		  }
		]
	  },
	  tooltips: {
		callbacks: {
		  label: function(item) {
			// yLabels are reversed, and zero-indexed
			return days[7 - item.yLabel + 1] + " " + item.xLabel + ":00";
		  }
		}
	  }
	},
	datasets: [
	  {
		label: undefined,
		fill: false,
		lineTension: 0.1,
		backgroundColor: 'rgba(75,192,192,0.4)',
		borderColor: 'rgba(75,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(75,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(75,192,192,1)',
		pointHoverBorderColor: 'rgba(220,220,220,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 1,
		pointHitRadius: 10,
		data: [{"y":7,"x":"0","r":9},{"y":7,"x":"2","r":6},{"y":7,"x":4,"r":0},{"y":7,"x":6,"r":0},{"y":7,"x":"8","r":38},{"y":7,"x":"10","r":28},{"y":7,"x":"12","r":3},{"y":7,"x":"14","r":24},{"y":7,"x":"16","r":12},{"y":7,"x":"18","r":37},{"y":7,"x":"20","r":14},{"y":7,"x":"22","r":19},{"y":7,"x":"24","r":58}]
	  }
	]
});

var days = {
	1: 'Svētdiena',
	2: 'Pirmdiena',
	3: 'Otrdiena',
	4: 'Trešdiena',
	5: 'Ceturtdiena',
	6: 'Piektdiena',
	7: 'Sestdiena'
};
 */
const Dashboard = () => {
	const [active, setActive] = useState('30d');
	const [loading, setLoading] = useState(false);
	const [hasBeenLoaded, setHasBeenLoaded] = useState(false);
	const [data, setData] = useState([{dates: [], values: []},{dates: [], values: []},{dates: [], values: []}])

	useEffect(() => {
		setLoading(true);
		api.tool.graphdata(active).then(res => {
			setHasBeenLoaded(true);
			setData(res)
			console.log(res)
		}).finally(() => setLoading(false))

	}, [active])

	const [pirmais, otrais, tresais] = data;

	if (!hasBeenLoaded && loading) {
		return <Spinner />;
	}

	return (
		<>
			<div className={styles.buttonGroup}>
				<ButtonGroup fullWidth variant='contained'>
					<Tooltip title='Pēdējās 7 dienas'>
						<Button
							className={active === '7d' ? styles.activeButton : styles.button}
							onClick={() => setActive('7d')}>
							7 d
						</Button>
					</Tooltip>
					<Tooltip title='Pēdējās 30 dienas'>
						<Button
							className={active === '30d' ? styles.activeButton : styles.button}
							onClick={() => setActive('30d')}>
							30 d
						</Button>
					</Tooltip>
					<Tooltip title='Šis gads'>
						<Button
							className={active === 'currY' ? styles.activeButton : styles.button}
							onClick={() => setActive('currY')}>
							Curr&nbsp;y
						</Button>
					</Tooltip>
					<Tooltip title='Viss periods'>
						<Button
							className={active === 'all' ? styles.activeButton : styles.button}
							onClick={() => setActive('all')}>
							All
						</Button>
					</Tooltip>
				</ButtonGroup>
			</div>
			<div style={{ clear: 'both' }} />
			<div className={styles.wrapper}>
				<div className={styles.graph1}>
					<Typography component='h4'>Pārbaudāmo rakstu izmaiņa</Typography>
					{pirmais.values.length> 0 && <Line
						data={optionsParbaudamie({
							dates: pirmais.dates,
							views: pirmais.values,
							bg: 'rgb(255, 99, 132)',
							border: 'rgb(255, 99, 132)',
							label: 'Atlikušie raksti'
						})}
					/>}
				</div>
				<div className={styles.graph1}>
					<Typography component='h4'>Pārbaudīto rakstu izmaiņa</Typography>
					{otrais.values.length> 0 && <Line
						data={optionsParbaudamie({
							dates: otrais.dates,
							views: otrais.values,
							bg: 'rgb(75, 192, 192)',
							border: 'rgb(75, 192, 192)',
							label: 'Pārbaudītie raksti'
						})}
					/>}
				</div>
				<div className={styles.graph2}>
					<Typography component='h4'>Vēl pārbaudāmo rakstu sadalījums</Typography>
					{tresais.values.length> 0 && <Line
						data={optionsParbaudamie({
							dates: tresais.dates,
							views: tresais.values,
							bg: 'rgb(255, 159, 64)',
							border: 'rgb(255, 159, 64)',
							label: 'Izveidotie raksti'
						})}
						options={{ maintainAspectRatio: false }}
					/>}
				</div>
				{/* <div className={styles.graph1}>
			<Bubble data={optionsBubble({dates: tresais[0], views: tresais[1]})} />
			</div> */}
			</div>
		</>
	);

};

export default Dashboard
